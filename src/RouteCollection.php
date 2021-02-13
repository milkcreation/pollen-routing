<?php

declare(strict_types=1);

namespace Pollen\Routing;

use League\Route\Router as BaseRouteCollection;

class RouteCollection extends BaseRouteCollection implements RouteCollectionInterface
{
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
}