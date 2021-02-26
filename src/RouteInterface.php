<?php

declare(strict_types=1);

namespace Pollen\Routing;

use League\Route\Route as BaseRoute;
use League\Route\RouteGroup as BaseRouteGroup;
use Pollen\Support\Proxy\ContainerProxyInterface;

/**
 * @mixin \League\Route\Route
 * @mixin RouteCollectorAwareTrait
 */
interface RouteInterface extends ContainerProxyInterface, RouteAwareInterface
{
    /**
     * Définition du groupe parent.
     *
     * @param BaseRouteGroup $group
     *
     * @return BaseRoute
     */
    public function setParentGroup(BaseRouteGroup $group): BaseRoute;
}