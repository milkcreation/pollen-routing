<?php

declare(strict_types=1);

namespace Pollen\Routing;

/**
 * @mixin \League\Route\Route
 */
interface RouteCollectionInterface
{

    /**
     * Déclaration d'un groupe.
     *
     * @param RouteGroupInterface $group
     *
     * @return RouteCollectionInterface
     */
    public function addGroup(RouteGroupInterface $group): RouteCollectionInterface;

    /**
     * Déclaration d'une route.
     *
     * @param RouteInterface $route
     *
     * @return RouteCollectionInterface
     */
    public function addRoute(RouteInterface $route): RouteCollectionInterface;

}