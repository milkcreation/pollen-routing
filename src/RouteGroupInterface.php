<?php

declare(strict_types=1);

namespace Pollen\Routing;

use League\Route\Route;
use Pollen\Support\Proxy\ContainerProxyInterface;

/**
 * @mixin \League\Route\RouteGroup
 * @mixin RouteCollectorAwareTrait
 */
interface RouteGroupInterface extends ContainerProxyInterface
{
    /**
     * @param string $method
     * @param string $path
     * @param $handler
     *
     * @return RouteInterface|Route
     */
    public function map(string $method, string $path, $handler): Route;
}