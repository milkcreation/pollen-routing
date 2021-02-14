<?php

declare(strict_types=1);

namespace Pollen\Routing;

use League\Route\Route as BaseRoute;
use League\Route\RouteGroup as BaseRouteGroup;

/**
 * @mixin \League\Route\Route
 * @mixin RouteCollectorAwareTrait
 * @mixin \Pollen\Support\Concerns\ContainerAwareTrait
 */
interface RouteInterface extends RouteAwareInterface
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