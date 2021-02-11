<?php

declare(strict_types=1);

namespace Pollen\Routing;

use Pollen\Routing\Concerns\StrategyAwareTrait;
use Pollen\Support\Concerns\ContainerAwareTrait;
use League\Route\Route as BaseRoute;

class Route extends BaseRoute
{
    use ContainerAwareTrait;
    use StrategyAwareTrait;
}