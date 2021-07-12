<?php

declare(strict_types=1);

namespace Pollen\Routing;

use League\Route\Route as BaseRoute;
use League\Route\RouteGroup as BaseRouteGroup;
use Pollen\Support\Proxy\ContainerProxy;

class Route extends BaseRoute implements RouteInterface
{
    use ContainerProxy;
    use RouteCollectorAwareTrait;

    /**
     * @inheritDoc
     */
    public function setParentGroup(BaseRouteGroup $group): BaseRoute
    {
        $this->group = $group;

        return $this;
    }
}