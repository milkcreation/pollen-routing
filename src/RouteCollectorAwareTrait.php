<?php

declare(strict_types=1);

namespace Pollen\Routing;

use League\Route\Route;
use InvalidArgumentException;
use Pollen\Routing\Middleware\XhrMiddleware;
use RuntimeException;

trait RouteCollectorAwareTrait
{
    /**
     * Register one or many middleware provides by the dependency injection container.
     *
     * @param string|string[] $aliases
     *
     * @return static
     */
    public function middle($aliases): self
    {
        if (!$this->getContainer()) {
            throw new RuntimeException('Middleware aliased declaration require dependency injection container.');
        }

        if (is_array($aliases)) {
            foreach ($aliases as $alias) {
                $this->middle($alias);
            }
            return $this;
        }

        if (is_string($aliases)) {
            $alias = $aliases;
        } else {
            throw new RuntimeException('Middleware alias must be a string type.');
        }

        if (!$this->getContainer()->has($alias)){
            $alias = "routing.middleware.$alias";
        }

        if (!$this->getContainer()->has($alias)) {
            throw new InvalidArgumentException(
                sprintf('Middleware alias [%s] is not being managed by the container.', $alias)
            );
        }

        $this->lazyMiddleware($alias);

        return $this;
    }

    /**
     * Register one or many strategy provides by the dependency injection container.
     *
     * @param string $alias
     *
     * @return static
     */
    public function strategy(string $alias): self
    {
        if (!$this->getContainer()) {
            throw new RuntimeException('Strategy aliased declaration require dependency injection container');
        }

        if (!$this->getContainer()->has("routing.strategy.$alias")) {
            throw new InvalidArgumentException(
                sprintf('Strategy alias (%s) is not being managed by the container', $alias)
            );
        }

        $this->setStrategy($this->getContainer()->get("routing.strategy.$alias"));

        return $this;
    }

    /**
     * Add a route for DELETE HTTP method.
     *
     * @param string $path
     * @param string|callable $handler
     *
     * @return RouteInterface|Route
     */
    public function delete(string $path, $handler): Route
    {
        return $this->map('DELETE', $path, $handler);
    }

    /**
     * Add a route for GET HTTP method.
     *
     * @param string $path
     * @param string|callable $handler
     *
     * @return RouteInterface|Route
     */
    public function get(string $path, $handler): Route
    {
        return $this->map('GET', $path, $handler);
    }

    /**
     * Add a route for OPTIONS HTTP method.
     *
     * @param string $path
     * @param string|callable $handler
     *
     * @return RouteInterface|Route
     */
    public function options(string $path, $handler): Route
    {
        return $this->map('OPTIONS', $path, $handler);
    }

    /**
     * Add a route for PATCH HTTP method.
     *
     * @param string $path
     * @param string|callable $handler
     *
     * @return RouteInterface|Route
     */
    public function patch(string $path, $handler): Route
    {
        return $this->map('PATCH', $path, $handler);
    }

    /**
     * Add a route for POST HTTP method.
     *
     * @param string $path
     * @param string|callable $handler
     *
     * @return RouteInterface|Route
     */
    public function post(string $path, $handler): Route
    {
        return $this->map('POST', $path, $handler);
    }

    /**
     * Add a route for PUT HTTP method.
     *
     * @param string $path
     * @param string|callable $handler
     *
     * @return RouteInterface|Route
     */
    public function put(string $path, $handler): Route
    {
        return $this->map('PUT', $path, $handler);
    }

    /**
     * Add a route for XMLHttpRequest.
     *
     * @param string $path
     * @param string|callable $handler
     * @param string $method
     *
     * @return RouteInterface
     */
    public function xhr(string $path, $handler, string $method = 'POST'): RouteInterface
    {
        $route = $this->map($method, $path, $handler);

        try {
            $route->middle('xhr');
        } catch(RuntimeException $e) {
            $route->middleware(new XhrMiddleware());
        }

        return $route;
    }
}