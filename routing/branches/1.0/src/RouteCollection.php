<?php

declare(strict_types=1);

namespace Pollen\Routing;

use Exception;
use FastRoute\RouteCollector;
use League\Route\Dispatcher;
use League\Route\Router as BaseRouteCollection;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class RouteCollection extends BaseRouteCollection implements RouteCollectionInterface
{
    /**
     * @var RouterInterface
     */
    protected $router;

    /**
     * @param RouterInterface $router
     * @param RouteCollector|null $routeCollector
     */
    public function __construct(RouterInterface $router, ?RouteCollector $routeCollector = null)
    {
        $this->router = $router;

        parent::__construct($routeCollector);
    }

    /**
     * @inheritDoc
     */
    public function addGroup(RouteGroupInterface $group): RouteCollectionInterface
    {
        $this->groups[] = $group;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function addRoute(RouteInterface $route): RouteCollectionInterface
    {
        $this->routes[] = $route;

        return $this;
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