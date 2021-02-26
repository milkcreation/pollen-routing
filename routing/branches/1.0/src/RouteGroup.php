<?php

declare(strict_types=1);

namespace Pollen\Routing;

use League\Route\Route;
use League\Route\RouteGroup as BaseRouteGroup;
use Pollen\Support\Proxy\ContainerProxy;

class RouteGroup extends BaseRouteGroup implements RouteGroupInterface
{
    use ContainerProxy;
    use RouteCollectorAwareTrait;

    /**
     * @var RouterInterface
     */
    protected $router;

    /**
     * @param string $prefix
     * @param callable $callback
     * @param RouterInterface $router
     */
    public function __construct(string $prefix, callable $callback, RouterInterface $router)
    {
        $this->router = $router;

        parent::__construct($prefix, $callback, $router->getRouteCollector());
    }

    /**
     * {@inheritDoc}
     *
     * @return RouteInterface|Route
     */
    public function map(string $method, string $path, $handler): Route
    {
        $path  = ($path === '/') ? $this->prefix : $this->prefix . sprintf('/%s', ltrim($path, '/'));
        $route = $this->router->map($method, $path, $handler);

        $route->setParentGroup($this);

        if ($host = $this->getHost()) {
            $route->setHost($host);
        }

        if ($scheme = $this->getScheme()) {
            $route->setScheme($scheme);
        }

        if ($port = $this->getPort()) {
            $route->setPort($port);
        }

        if ($route->getStrategy() === null && $this->getStrategy() !== null) {
            $route->setStrategy($this->getStrategy());
        }

        return $route;
    }
}