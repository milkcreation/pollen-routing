<?php

declare(strict_types=1);

namespace Pollen\Routing;

use League\Route\RouteCollectionInterface as BaseRouteCollectionInterface;
use Psr\Http\Message\ResponseInterface as PsrResponse;
use Psr\Http\Message\ServerRequestInterface as PsrRequest;

/**
 * @mixin \League\Route\Route
 */
interface RouteCollectionInterface extends BaseRouteCollectionInterface
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