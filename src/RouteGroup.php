<?php

declare(strict_types=1);

namespace Pollen\Routing;

use Pollen\Routing\Concerns\StrategyAwareTrait;
use Pollen\Support\Concerns\ContainerAwareTrait;
use League\Route\RouteGroup as BaseRouteGroup;

class RouteGroup extends BaseRouteGroup
{
    use ContainerAwareTrait;
    use StrategyAwareTrait;
}