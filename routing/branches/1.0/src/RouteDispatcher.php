<?php

declare(strict_types=1);

namespace Pollen\Routing;

use FastRoute\Dispatcher as FastRoute;
use League\Route\Dispatcher as BaseRouteDispatcher;
use Psr\Http\Message\ResponseInterface as PsrResponse;
use Psr\Http\Message\ServerRequestInterface as PsrRequest;

class RouteDispatcher extends BaseRouteDispatcher
{
    /**
     * Router instance.
     * @var RouterInterface $router
     */
    protected RouterInterface $router;

    /**
     * @param RouterInterface $router
     */
    public function __construct(RouterInterface $router)
    {
        $this->router = $router;

        parent::__construct($router->getRouteCollector()->getData());
    }

    /**
     * @param PsrRequest $request
     *
     * @return PsrResponse
     */
    public function dispatchRequest(PsrRequest $request): PsrResponse
    {
        $method = $request->getMethod();
        $uri    = $request->getUri()->getPath();
        $match  = $this->dispatch($method, $uri);

        switch ($match[0]) {
            case FastRoute::NOT_FOUND:
                $this->setNotFoundDecoratorMiddleware();
                break;
            case FastRoute::METHOD_NOT_ALLOWED:
                $allowed = (array) $match[1];
                $this->setMethodNotAllowedDecoratorMiddleware($allowed);
                break;
            case FastRoute::FOUND:
                $route = ($match[1] instanceof RouteInterface) ? $match[1] : new Route($method, $uri, $match[1]);
                $route->setVars($match[2]);

                $this->router->setCurrentRoute($route);

                if ($this->isExtraConditionMatch($route, $request)) {
                    $this->setFoundMiddleware($route);
                    $request = $this->requestWithRouteAttributes($request, $route);
                    break;
                }

                $this->setNotFoundDecoratorMiddleware();
                break;
        }

        return $this->handle($request);
    }
}