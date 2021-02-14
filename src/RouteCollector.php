<?php

declare(strict_types=1);

namespace Pollen\Routing;

use Exception;
use FastRoute\RouteCollector as FastRouteRouteCollector;
use League\Route\Dispatcher;
use League\Route\Router as BaseRouteCollector;
use League\Route\Strategy\OptionsHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class RouteCollector extends BaseRouteCollector implements RouteCollectorInterface
{
    /**
     * @var RouterInterface
     */
    protected $router;

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
    public function dispatch(ServerRequestInterface $request): ResponseInterface
    {
        if (false === $this->routesPrepared) {
            $this->prepareRoutes($request);
        }

        /** @var Dispatcher $dispatcher */
        $dispatcher = (new RouteDispatcher($this->routesData, $this->router))->setStrategy($this->getStrategy());

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

            return $route;
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
}