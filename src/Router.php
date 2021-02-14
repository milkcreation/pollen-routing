<?php

declare(strict_types=1);

namespace Pollen\Routing;

use Exception;
use FastRoute\BadRouteException;
use InvalidArgumentException;
use League\Route\Http\Exception\NotFoundException;
use Laminas\HttpHandlerRunner\Emitter\SapiEmitter;
use Pollen\Http\Request;
use Pollen\Http\RequestInterface;
use Pollen\Http\Response;
use Pollen\Http\ResponseInterface;
use Pollen\Routing\Strategy\ApplicationStrategy;
use Pollen\Support\Concerns\ConfigBagTrait;
use Pollen\Support\Concerns\ContainerAwareTrait;
use Psr\Container\ContainerInterface as Container;
use Psr\Http\Message\ResponseInterface as PsrResponse;
use RuntimeException;

class Router implements RouterInterface
{
    use ConfigBagTrait;
    use ContainerAwareTrait;
    use RouteCollectionAwareTrait;

    /**
     * Instance de la classe.
     * @var static|null
     */
    private static $instance;

    /**
     * @var string|null
     */
    private $basePrefixNormalized;

    /**
     * @var string
     */
    protected $basePrefix = '';

    /**
     * @var RouteInterface|null
     */
    protected $currentRoute;

    /**
     * @var callable
     */
    protected $fallback;

    /**
     * @var bool
     */
    protected $handled = false;

    /**
     * @var RouteCollectionInterface
     */
    protected $routeCollection;

    /**
     * @param array $config
     * @param Container|null $container
     */
    public function __construct(array $config = [], ?Container $container = null)
    {
        $this->setConfig($config);

        if (!is_null($container)) {
            $this->setContainer($container);
        }

        $this->routeCollection = new RouteCollection($this);

        $this->setBasePrefix(Request::getFromGlobals()->getRewriteBase());
    }

    /**
     * @inheritDoc
     */
    public function beforeSendResponse(PsrResponse $response): PsrResponse
    {
        try {
            /** @var MiddlewareInterface|null $middleware */
            $middleware = $this->getRouteCollection()->shiftMiddleware();
        } catch (Exception $e) {
            $middleware = null;
        }

        if (is_null($middleware)) {
            return $response;
        }

        return $middleware->beforeSend($response, $this) ?: $response;
    }

    /**
     * @inheritDoc
     */
    public function current(): ?RouteInterface
    {
        if (!$this->handled) {
            throw new RuntimeException('Request must be handled before requesting the current route');
        }
        return $this->currentRoute;
    }

    /**
     * @inheritDoc
     */
    public function currentRouteName(): ?string
    {
        return $this->currentRoute ? $this->currentRoute->getName() : null;
    }

    /**
     * @inheritDoc
     */
    public function getBasePrefix(): string
    {
        if ($this->basePrefixNormalized === null) {
            $this->basePrefixNormalized = $this->basePrefix ? '/' . rtrim(ltrim($this->basePrefix, '/'), '/') : '';
        }
        return $this->basePrefixNormalized;
    }

    /**
     * @inheritDoc
     */
    public function getFallbackCallable(): ?callable
    {
        if (!$callable = $this->fallback) {
            return null;
        }

        if (is_string($callable) && strpos($callable, '::') !== false) {
            $callable = explode('::', $callable);
        }

        if (is_array($callable) && isset($callable[0]) && is_object($callable[0])) {
            $callable = [$callable[0], $callable[1]];
        }

        if (is_array($callable) && isset($callable[0]) && is_string($callable[0])) {
            $callable = [$this->resolveFallbackClass($callable[0]), $callable[1]];
        }

        if (is_string($callable)) {
            $callable = $this->resolveFallbackClass($callable);
        }

        if (!is_callable($callable)) {
            throw new RuntimeException('Could not resolve a callable Route Fallback');
        }
        return $callable;
    }

    /**
     * @inheritDoc
     */
    public function getNamedRoute(string $name): ?RouteInterface
    {
        return $this->getRouteCollection()->getRoute($name);
    }

    /**
     * @inheritDoc
     */
    public function getRouteCollection(): RouteCollectionInterface
    {
        return $this->routeCollection;
    }

    /**
     * @inheritDoc
     */
    public function group(string $prefix, callable $group): RouteGroupInterface
    {
        $group = new RouteGroup($prefix, $group, $this);

        if ($container = $this->getContainer()) {
            $group->setContainer($container);
        }

        $this->routeCollection->addGroup($group);

        return $group;
    }

    public function head(string $path, $handler): RouteInterface
    {
        return $this->map('HEAD', $path, $handler);
    }

    /**
     * @inheritDoc
     */
    public function handleRequest(RequestInterface $request): ResponseInterface
    {
        try {
            $this->handled = true;

            if ($this->routeCollection->getStrategy() === null) {
                $strategy = new ApplicationStrategy();
                if ($container = $this->getContainer()) {
                    $strategy->setContainer($container);
                }
                $this->routeCollection->setStrategy($strategy);
            }

            $psrResponse = $this->routeCollection->dispatch($request->psr());

            return Response::createFromPsr($psrResponse);
        } catch (BadRouteException $e) {
            throw new RuntimeException(
                sprintf('Bad Route declaration thrown exception : [%s]', $e->getMessage())
            );
        } catch (Exception $e) {
            if ($e instanceof NotFoundException && ($fallback = $this->getFallbackCallable())) {
                $fallback = $this->getFallbackCallable();

                return $fallback();
            }
            throw new RuntimeException($e->getMessage());
        }
    }

    /**
     * @inheritDoc
     */
    public function map(string $method, string $path, $handler): RouteInterface
    {
        $path = $this->getBasePrefix() . sprintf('/%s', ltrim($path, '/'));
        $route = new Route($method, $path, $handler);

        if ($container = $this->getContainer()) {
            $route->setContainer($container);
        }

        $this->routeCollection->addRoute($route);

        return $route;
    }

    /**
     * Récupération de la classe de rappel.
     *
     * @param string $class
     *
     * @return object
     */
    protected function resolveFallbackClass(string $class): object
    {
        if (($container = $this->getContainer()) && $container->has($class)) {
            return $container->get($class);
        }

        if (class_exists($class)) {
            return new $class();
        }
        throw new RuntimeException('Route Fallback Class unresolvable');
    }

    protected function resolveMiddleware($middleware): MiddlewareInterface
    {
        $container = $this->getContainer();

        if ($container === null && is_string($middleware) && class_exists($middleware)) {
            $middleware = new $middleware();
        }

        if ($container !== null && is_string($middleware) && $container->has($middleware)) {
            $middleware = $container->get($middleware);
        }

        if ($middleware instanceof MiddlewareInterface) {
            return $middleware;
        }

        throw new InvalidArgumentException(sprintf('Could not resolve middleware class: %s', $middleware));
    }

    /**
     * @param ResponseInterface $response
     *
     * @return bool
     */
    public function sendResponse(ResponseInterface $response): bool
    {
        /*if ($dispatched = $this->router->getResponse()) {
            $additionnalHeaders = $dispatched->getHeaders() ?: [];
        }

        if (!empty($additionnalHeaders)) {
            foreach ($additionnalHeaders as $name => $value) {
                $psrResponse->withAddedHeader($name, $value);
            }
        }*/

        $collect = $this->getRouteCollection();

        foreach ($collect->getMiddlewareStack() as $middleware) {
            $collect->middleware($this->resolveMiddleware($middleware));
        }

        if ($route = $this->current()) {
            if ($group = $route->getParentGroup()) {
                foreach ($group->getMiddlewareStack() as $middleware) {
                    $collect->middleware($this->resolveMiddleware($middleware));
                }
            }

            foreach ($route->getMiddlewareStack() as $middleware) {
                $collect->middleware($this->resolveMiddleware($middleware));
            }
        }

        $response = $this->beforeSendResponse($response->psr());

        return (new SapiEmitter())->emit($response);
    }

    /**
     * @inheritDoc
     */
    public function setBasePrefix(string $basePrefix): RouterInterface
    {
        $this->basePrefix = $basePrefix;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setCurrentRoute(RouteInterface $route): RouterInterface
    {
        $this->currentRoute = $route;

        return $this;
    }

    /**
     * @param callable|string $fallback
     *
     * @return $this
     */
    public function setFallback($fallback): RouterInterface
    {
        $this->fallback = $fallback;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function terminateEvent(RequestInterface $request, ResponseInterface $response): void
    {
        exit;
    }
}