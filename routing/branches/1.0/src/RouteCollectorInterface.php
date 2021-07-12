<?php

declare(strict_types=1);

namespace Pollen\Routing;

use League\Route\RouteCollectionInterface as BaseRouteCollectorInterface;
use Psr\Http\Message\ResponseInterface as PsrResponse;
use Psr\Http\Message\ServerRequestInterface as PsrRequest;

/**
 * @mixin \League\Route\Route
 * @mixin \FastRoute\RouteCollector
 */
interface RouteCollectorInterface extends BaseRouteCollectorInterface
{
    /**
     * Add a new group by its instance.
     *
     * @param RouteGroupInterface $group
     *
     * @return RouteCollectorInterface
     */
    public function addGroup(RouteGroupInterface $group): RouteCollectorInterface;

    /**
     * Add a new route by its instance.
     *
     * @param RouteInterface $route
     *
     * @return RouteCollectorInterface
     */
    public function addRoute(RouteInterface $route): RouteCollectorInterface;

    /**
     * Route dispatching.
     *
     * @param PsrRequest $request
     *
     * @return PsrResponse
     */
    public function dispatch(PsrRequest $request): PsrResponse;

    /**
     * Get a route by its name.
     *
     * @param string $name
     *
     * @return RouteInterface|null
     */
    public function getRoute(string $name): ?RouteInterface;

    /**
     * Get routes url patterns.
     *
     * @return array
     */
    public function getUrlPatterns(): array;

    /**
     * Prepare registered routes.
     *
     * @param PsrRequest $request
     *
     * @return void
     */
    public function prepareRoutes(PsrRequest $request): void;
}