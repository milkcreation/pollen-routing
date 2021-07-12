<?php

declare(strict_types=1);

namespace Pollen\Routing;

use BadMethodCallException;
use Exception;
use FastRoute\RouteCollector as FastRouteRouteCollector;
use League\Route\Dispatcher;
use League\Route\Router as BaseRouteCollector;
use League\Route\Strategy\OptionsHandlerInterface;
use Pollen\Routing\Strategy\ApplicationStrategy;
use Psr\Http\Message\ResponseInterface as PsrResponse;
use Psr\Http\Message\ServerRequestInterface as PsrRequest;
use Throwable;

/**
 * @mixin FastRouteRouteCollector
 */
class RouteCollector extends BaseRouteCollector implements RouteCollectorInterface
{
    /**
     * Router instance.
     * @var RouterInterface
     */
    protected RouterInterface $router;

    /**
     * @param RouterInterface $router
     * @param FastRouteRouteCollector|null $routeCollector
     */
    public function __construct(RouterInterface $router, ?FastRouteRouteCollector $routeCollector = null)
    {
        $this->router = $router;

        parent::__construct($routeCollector);
    }

    /**
     * Delegate method call of the route collector.
     *
     * @param string $method
     * @param array $arguments
     *
     * @return mixed
     *
     * @throws Exception
     */
    public function __call(string $method, array $arguments)
    {
        try {
            return $this->routeCollector->{$method}(...$arguments);
        } catch (Exception $e) {
            throw $e;
        } catch (Throwable $e) {
            throw new BadMethodCallException(
                sprintf(
                    'Delegate RouteCollector method call [%s] throws an exception: %s',
                    $method,
                    $e->getMessage()
                ), 0, $e
            );
        }
    }

    /**
     * @inheritDoc
     */
    public function addGroup(RouteGroupInterface $group): RouteCollectorInterface
    {
        $this->groups[] = $group;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function addRoute(RouteInterface $route): RouteCollectorInterface
    {
        $this->routes[] = $route;

        return $this;
    }

    protected function buildOptionsRoutes(array $options): void
    {
        if (!($this->getStrategy() instanceof OptionsHandlerInterface)) {
            return;
        }

        /** @var OptionsHandlerInterface $strategy */
        $strategy = $this->getStrategy();

        foreach ($options as $identifier => $methods) {
            [$scheme, $host, $port, $path] = explode(static::IDENTIFIER_SEPARATOR, $identifier);
            $route = new Route('OPTIONS', $path, $strategy->getOptionsCallable($methods));

            if (!empty($scheme)) {
                $route->setScheme($scheme);
            }

            if (!empty($host)) {
                $route->setHost($host);
            }

            if (!empty($port)) {
                $route->setPort($port);
            }

            $this->routeCollector->addRoute($route->getMethod(), $this->parseRoutePath($route->getPath()), $route);
        }
    }

    /**
     * @inheritDoc
     */
    public function dispatch(PsrRequest $request): PsrResponse
    {
        if (false === $this->routesPrepared) {
            $this->prepareRoutes($request);
        }

        /** @var Dispatcher $dispatcher */
        $dispatcher = (new RouteDispatcher($this->router))->setStrategy($this->getStrategy());

        foreach ($this->getMiddlewareStack() as $middleware) {
            if (is_string($middleware)) {
                $dispatcher->lazyMiddleware($middleware);
                continue;
            }

            $dispatcher->middleware($middleware);
        }

        return $dispatcher->dispatchRequest($request);
    }

    /**
     * @inheritDoc
     */
    public function getRoute(string $name): ?RouteInterface
    {
        try {
            /** @var RouteInterface $route */
            $route = $this->getNamedRoute($name);

             $route;
        } catch(Exception $e) {
            return null;
        }
    }

    /**
     * @inheritDoc
     */
    public function getUrlPatterns(): array
    {
        return $this->patternMatchers;
    }

    /**
     * @inheritDoc
     */
    public function prepareRoutes(PsrRequest $request): void
    {
        if ($this->getStrategy() === null) {
            $strategy = new ApplicationStrategy();

            if ($container = $this->router->getContainer()) {
                $strategy->setContainer($container);
            }
            $this->setStrategy($strategy);
        }

        parent::prepareRoutes($request);
    }
}