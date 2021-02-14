<?php

declare(strict_types=1);

namespace Pollen\Routing;

use League\Route\RouteCollectionInterface as BaseRouteCollectorInterface;
use Psr\Http\Message\ResponseInterface as PsrResponse;
use Psr\Http\Message\ServerRequestInterface as PsrRequest;

/**
 * @mixin \League\Route\Route
 */
interface RouteCollectorInterface extends BaseRouteCollectorInterface
{

    /**
     * Déclaration d'un groupe.
     *
     * @param RouteGroupInterface $group
     *
     * @return RouteCollectorInterface
     */
    public function addGroup(RouteGroupInterface $group): RouteCollectorInterface;

    /**
     * Déclaration d'une route.
     *
     * @param RouteInterface $route
     *
     * @return RouteCollectorInterface
     */
    public function addRoute(RouteInterface $route): RouteCollectorInterface;

    /**
     * Répartiteur.
     *
     * @param PsrRequest $request
     *
     * @return PsrResponse
     */
    public function dispatch(PsrRequest $request): PsrResponse;

    /**
     * Récupération d'un route qualifiée
     *
     * @param string $name
     *
     * @return RouteInterface|null
     */
    public function getRoute(string $name): ?RouteInterface;

    /**
     * Récupération des motifs d'urls déclarés.
     *
     * @return array
     */
    public function getUrlPatterns(): array;
}