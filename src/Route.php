<?php

declare(strict_types=1);

namespace Pollen\Routing;

use Pollen\Support\Concerns\ContainerAwareTrait;
use League\Route\Route as BaseRoute;
use League\Route\RouteGroup as BaseRouteGroup;

class Route extends BaseRoute implements RouteInterface
{
    use ContainerAwareTrait;
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