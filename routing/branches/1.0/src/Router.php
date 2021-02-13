<?php

declare(strict_types=1);

namespace Pollen\Routing;

use Exception;
use FastRoute\BadRouteException;
use League\Route\Http\Exception\NotFoundException;
use Laminas\HttpHandlerRunner\Emitter\SapiEmitter;
use Pollen\Http\Request;
use Pollen\Http\RequestInterface;
use Pollen\Http\Response;
use Pollen\Http\ResponseInterface;
use Pollen\Routing\Concerns\RouteCollectionTrait;
use Pollen\Routing\Strategy\ApplicationStrategy;
use Pollen\Support\Concerns\ConfigBagTrait;
use Pollen\Support\Concerns\ContainerAwareTrait;
use Psr\Container\ContainerInterface as Container;
use RuntimeException;

class Router implements RouterInterface
{
    use ConfigBagTrait;
    use ContainerAwareTrait;
    use RouteCollectionTrait;

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
     * @var callable
     */
    protected $fallback;

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

        if (!self::$instance instanceof static) {
            self::$instance = $this;
        }

        $this->routeCollection = new RouteCollection();

        $this->setBasePrefix(Request::getFromGlobals()->getRewriteBase());
    }

    /**
     * @inheritDoc
     */
    public static function instance(): RouterInterface
    {
        if (self::$instance instanceof self) {
            return self::$instance;
        }
        throw new RuntimeException(sprintf('Unavailable %s instance', __CLASS__));
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
    public function getFallbackCallable()
    {
        $callable = $this->fallback;

        if (is_string($callable) && strpos($callable, '::') !== false) {
            $callable = explode('::', $callable);
        }

        if (is_array($callable) && isset($callable[0]) && is_object($callable[0])) {
            $callable = [$callable[0], $callable[1]];
        }

        if (is_array($callable) && isset($callable[0]) && is_string($callable[0])) {
            $callable = [$this->resolveFallback($callable[0]), $callable[1]];
        }

        if (is_string($callable)) {
            $callable = $this->resolveFallback($callable);
        }

        if (!is_callable($callable)) {
            throw new RuntimeException('Could not resolve a callable fallback');
        }
        return $callable;
    }

    /**
     * @inheritDoc
     */
    public function group(string $prefix, callable $group): RouteGroupInterface
    {
        $group = new RouteGroup($prefix, $group, $this->routeCollection);

        if ($container = $this->getContainer()) {
            $group->setContainer($container);
        }

        $this->routeCollection->addGroup($group);

        return $group;
    }

    /**
     * @inheritDoc
     */
    public function handleRequest(RequestInterface $request): ResponseInterface
    {
        try {
            if ($this->routeCollection->getStrategy() === null) {
                $this->routeCollection->setStrategy(new ApplicationStrategy());
            }

            $psrResponse = $this->routeCollection->dispatch($request->psr());

            return Response::createFromPsr($psrResponse);
        } catch (BadRouteException $e) {
            throw new RuntimeException(
                sprintf('Bad Route declaration thrown exception : [%s]', $e->getMessage())
            );
        } catch(Exception $e) {
            if ($e instanceof NotFoundException) {
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
     * @todo
     */
    protected function resolveFallback(string $class)
    {
        if (($container = $this->getContainer()) && $container->has($class)) {
            return $container->get($class);
        }

        if (class_exists($class)) {
            return new $class();
        }

        return $class;
    }

    /**
     * @param ResponseInterface $response
     *
     * @return bool
     */
    public function sendResponse(ResponseInterface $response): bool
    {
        return (new SapiEmitter())->emit($response->psr());
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
     * @param callable|string $fallback
     *
     * @return $this
     */
    public function setFallback($fallback): RouterInterface
    {
        $this->fallback = $fallback;

        return $this;
    }
}