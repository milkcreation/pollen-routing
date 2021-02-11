<?php

declare(strict_types=1);

namespace Pollen\Routing;

use League\Route\Middleware\MiddlewareAwareInterface;
use League\Route\Route as BaseRoute;
use League\Route\RouteCollectionInterface;
use League\Route\RouteGroup as BaseRouteGroup;
use League\Route\Strategy\StrategyAwareInterface;
use Psr\Http\Message\ResponseInterface as PsrResponse;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * @mixin \Pollen\Routing\Concerns\RouteCollectionTrait
 * @mixin \Pollen\Support\Concerns\ContainerAwareTrait
 * @mixin \League\Route\Router
 */
interface RouterInterface extends
    MiddlewareAwareInterface,
    RouteCollectionInterface,
    StrategyAwareInterface,
    RequestHandlerInterface
{
    /**
     * Récupération du préfixe de base des chemins de route.
     *
     * @return string
     */
    public function getBasePrefix(): string;

    /**
     * {@inheritDoc}
     *
     * @return RouteGroup
     */
    public function group(string $prefix, callable $group): BaseRouteGroup;

    /**
     * {@inheritDoc}
     *
     * @return Route
     */
    public function map(string $method, string $path, $handler): BaseRoute;

    /**
     * Envoi de la réponse
     *
     * @param PsrResponse $response
     *
     * @return bool
     */
    public function send(PsrResponse $response): bool;

    /**
     * Définition du préfixe de base des chemins de route.
     *
     * @param string $basePrefix
     *
     * @return static
     */
    public function setBasePrefix(string $basePrefix): RouterInterface;
}