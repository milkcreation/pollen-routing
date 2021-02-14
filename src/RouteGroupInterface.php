<?php

declare(strict_types=1);

namespace Pollen\Routing;

use League\Route\Route;

/**
 * @mixin \League\Route\RouteGroup
 * @mixin RouteCollectionAwareTrait
 * @mixin \Pollen\Support\Concerns\ContainerAwareTrait
 */
interface RouteGroupInterface
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