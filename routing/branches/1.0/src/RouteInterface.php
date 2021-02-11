<?php

declare(strict_types=1);

namespace Pollen\Routing;

use League\Route\Middleware\MiddlewareAwareInterface;
use League\Route\RouteConditionHandlerInterface;
use League\Route\Strategy\StrategyAwareInterface;
use Psr\Http\Server\MiddlewareInterface;

/**
 * @mixin \League\Route\Route
 * @mixin \Pollen\Support\Concerns\ContainerAwareTrait;
 * @mixin \Pollen\Routing\Concerns\StrategyAwareTrait
 */
interface RouteInterface extends MiddlewareInterface, MiddlewareAwareInterface, RouteConditionHandlerInterface, StrategyAwareInterface
{

}