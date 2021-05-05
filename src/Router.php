<?php

declare(strict_types=1);

namespace Pollen\Routing;

use Exception;
use FastRoute\BadRouteException;
use InvalidArgumentException;
use Laminas\HttpHandlerRunner\Emitter\SapiEmitter;
use League\Route\Middleware\MiddlewareAwareTrait;
use League\Route\Http\Exception\HttpExceptionInterface as BaseHttpExceptionInterface;
use Pollen\Http\RedirectResponse;
use Pollen\Http\RedirectResponseInterface;
use Pollen\Http\Request;
use Pollen\Http\RequestInterface;
use Pollen\Http\Response;
use Pollen\Http\ResponseInterface;
use Pollen\Support\Concerns\ConfigBagAwareTrait;
use Pollen\Support\Exception\ManagerRuntimeException;
use Pollen\Support\Proxy\ContainerProxy;
use Pollen\Support\Proxy\HttpRequestProxy;
use Pollen\Routing\Exception\HttpExceptionInterface;
use Psr\Container\ContainerInterface as Container;
use Psr\Http\Message\ResponseInterface as PsrResponse;
use Psr\Http\Server\MiddlewareInterface as BaseMiddlewareInterface;
use RuntimeException;
use Throwable;

class Router implements RouterInterface
{
    use ConfigBagAwareTrait;
    use ContainerProxy;
    use HttpRequestProxy;
    use MiddlewareAwareTrait;
    use RouteCollectorAwareTrait;

    /**
     * Instance principale.
     * @var static|null
     */
    private static $instance;

    /**
     * @var string|null
     */
    private $basePrefixNormalized;

    /**
     * @var string|null
     */
    protected $basePrefix;

    /**
     * @var RouteInterface|null
     */
    protected $currentRoute;

    /**
     * @var callable
     */
    protected $fallback;

    /**
     * @var RequestInterface|null
     */
    protected $handleRequest;

    /**
     * @var RouteCollectorInterface
     */
    protected $routeCollector;

    /**
     * @param array $config
     * @param Container|null $container
     */
    public function __construct(array $config = [], ?Container $container = null)
    {
        $this->setConfig($config);

        if ($container !== null) {
            $this->setContainer($container);
        }

        $this->routeCollector = new RouteCollector($this);

        $this->setBasePrefix(Request::getFromGlobals()->getRewriteBase());

        if (!self::$instance instanceof static) {
            self::$instance = $this;
        }
    }

    /**
     * Récupération de l'instance principale.
     *
     * @return static
     */
    public static function getInstance(): RouterInterface
    {
        if (self::$instance instanceof self) {
            return self::$instance;
        }
        throw new ManagerRuntimeException(sprintf('Unavailable [%s] instance', __CLASS__));
    }

    /**
     * @inheritDoc
     */
    public function addRoute(RouteInterface $route): RouterInterface
    {
        $this->routeCollector->addRoute($route);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function beforeSendResponse(PsrResponse $response): PsrResponse
    {
        try {
            /** @var MiddlewareInterface|null $middleware */
            $middleware = $this->getRouteCollector()->shiftMiddleware();
        } catch (Exception $e) {
            $middleware = null;
        }

        if ($middleware === null) {
            return $response;
        }

        return $middleware->beforeSend($response, $this) ?: $response;
    }

    /**
     * @inheritDoc
     */
    public function current(): ?RouteInterface
    {
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
    public function getHandleRequest(): RequestInterface
    {
        if ($this->handleRequest === null) {
            $this->handleRequest = $this->httpRequest();
        }

        return $this->handleRequest;
    }

    /**
     * @inheritDoc
     */
    public function hasFallback(): bool
    {
        return $this->fallback !== null;
    }

    /**
     * @inheritDoc
     */
    public function getNamedRoute(string $name): ?RouteInterface
    {
        return $this->getRouteCollector()->getRoute($name);
    }

    /**
     * @inheritDoc
     */
    public function getNamedRouteRedirect(
        string $name,
        array $args = [],
        bool $isAbsolute = false,
        int $status = 302,
        array $headers = []
    ): RedirectResponseInterface {
        if ($route = $this->getNamedRoute($name)) {
            return $this->getRouteRedirect($route, $args, $isAbsolute, $status, $headers);
        }
        return new RedirectResponse('', $status, $headers);
    }

    /**
     * @inheritDoc
     */
    public function getNamedRouteUrl(string $name, array $args = [], bool $isAbsolute = false): ?string
    {
        if ($route = $this->getNamedRoute($name)) {
            return $this->getRouteUrl($route, $args, $isAbsolute);
        }
        return null;
    }

    /**
     * @inheritDoc
     */
    public function getRouteCollector(): RouteCollectorInterface
    {
        return $this->routeCollector;
    }

    /**
     * @inheritDoc
     */
    public function getRouteRedirect(
        RouteInterface $route,
        array $args = [],
        bool $isAbsolute = false,
        int $status = 302,
        array $headers = []
    ): RedirectResponseInterface {
        if ($url = $this->getRouteUrl($route, $args, $isAbsolute)) {
            return new RedirectResponse($url, $status, $headers);
        }
        return new RedirectResponse('', $status, $headers);
    }

    /**
     * @inheritDoc
     */
    public function getRouteUrl(RouteInterface $route, array $args = [], bool $isAbsolute = false): ?string
    {
        try {
            $generator = new UrlGenerator($route->getPath(), $this->handleRequest);

            return $generator
                ->setAbsoluteEnabled($isAbsolute)
                ->setHost($route->getHost())
                ->setPort($route->getPort())
                ->setScheme($route->getScheme())
                ->setUrlPatterns($this->getRouteCollector()->getUrlPatterns())
                ->get($args);
        } catch (Exception $e) {
            return null;
        }
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

        $this->routeCollector->addGroup($group);

        return $group;
    }

    public function head(string $path, $handler): RouteInterface
    {
        return $this->map('HEAD', $path, $handler);
    }

    /**
     * @inheritDoc
     */
    public function handleRequest(): ResponseInterface
    {
        try {
            $request = $this->getHandleRequest();

            $psrResponse = $this->routeCollector->dispatch($request->psr());

            return Response::createFromPsr($psrResponse);
        } catch (BadRouteException $e) {
            throw new RuntimeException(
                sprintf('Bad Route declaration thrown exception : [%s]', $e->getMessage())
            );
        } catch (HttpExceptionInterface|BaseHttpExceptionInterface $e) {
            if ($fallback = $this->getFallbackCallable()) {
                return $fallback($e);
            }
            return new Response($e->getMessage(), $e->getStatusCode());
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

        $this->addRoute($route);

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

    protected function resolveMiddleware($middleware): BaseMiddlewareInterface
    {
        $container = $this->getContainer();

        if ($container === null && is_string($middleware) && class_exists($middleware)) {
            $middleware = new $middleware();
        }

        if ($container !== null && is_string($middleware) && $container->has($middleware)) {
            $middleware = $container->get($middleware);
        }

        if ($middleware instanceof BaseMiddlewareInterface) {
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
        $collect = $this->getRouteCollector();

        foreach ($this->getMiddlewareStack() as $middleware) {
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

        $psrResponse = $this->beforeSendResponse($response->psr());

        return (new SapiEmitter())->emit($psrResponse);
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
     * @param RequestInterface $handleRequest
     *
     * @return $this
     */
    public function setHandleRequest(RequestInterface $handleRequest): RouterInterface
    {
        $this->handleRequest = $handleRequest;

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