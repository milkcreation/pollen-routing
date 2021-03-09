<?php

declare(strict_types=1);

namespace Pollen\Routing;

use League\Route\Middleware\MiddlewareAwareInterface;
use Pollen\Http\RedirectResponseInterface;
use Pollen\Http\RequestInterface;
use Pollen\Http\ResponseInterface;
use Pollen\Support\Concerns\ConfigBagAwareTraitInterface;
use Pollen\Support\Proxy\ContainerProxyInterface;
use Psr\Http\Message\ResponseInterface as PsrResponse;

/**
 * @mixin RouteCollectorAwareTrait
 */
interface RouterInterface extends ConfigBagAwareTraitInterface, ContainerProxyInterface, MiddlewareAwareInterface
{
    /**
     * Déclaration d'une route.
     *
     * @param RouteInterface $route
     *
     * @return static
     */
    public function addRoute(RouteInterface $route): RouterInterface;

    /**
     * Pré-traitement de l'envoi de la réponse HTTP.
     *
     * @param PsrResponse $response
     *
     * @return PsrResponse
     */
    public function beforeSendResponse(PsrResponse $response): PsrResponse;

    /**
     * Récupération de la route courante.
     *
     * @return RouteInterface|null
     */
    public function current(): ?RouteInterface;

    /**
     * Récupération de l'intitulé d'une route qualifiée.
     *
     * @return string
     */
    public function currentRouteName(): ?string;

    /**
     * Récupération du préfixe de base des chemins de route.
     *
     * @return string
     */
    public function getBasePrefix(): string;

    /**
     * Récupération de la fonction de rappel.
     *
     * @return callable|null
     */
    public function getFallbackCallable(): ?callable;

    /**
     * Récupération d'une route qualifiée.
     *
     * @param string $name
     *
     * @return RouteInterface|null
     */
    public function getNamedRoute(string $name): ?RouteInterface;

    /**
     * Récupération de la réponse HTTP de redirection vers une route qualifiée.
     *
     * @param string $name
     * @param array $args
     * @param bool $isAbsolute
     * @param int $status
     * @param array $headers
     *
     * @return RedirectResponseInterface
     */
    public function getNamedRouteRedirect(
        string $name,
        array $args = [],
        bool $isAbsolute = false,
        int $status = 302,
        array $headers = []
    ): RedirectResponseInterface;

    /**
     * Récupération de l'url d'une route qualifiée.
     *
     * @param string $name
     * @param array $args
     * @param bool $isAbsolute
     *
     * @return string
     */
    public function getNamedRouteUrl(string $name, array $args = [], bool $isAbsolute = false): ?string;

    /**
     * Récupération de l'instance du gestionnaire de la collection de routes.
     *
     * @return RouteCollectorInterface
     */
    public function getRouteCollector(): RouteCollectorInterface;

    /**
     * Récupération de la réponse HTTP de redirection vers une route.
     *
     * @param RouteInterface $route
     * @param array $args
     * @param bool $isAbsolute
     * @param int $status
     * @param array $headers
     *
     * @return RedirectResponseInterface
     */
    public function getRouteRedirect(
        RouteInterface $route,
        array $args = [],
        bool $isAbsolute = false,
        int $status = 302,
        array $headers = []
    ): RedirectResponseInterface;

    /**
     * Récupération de l'url d'une route.
     *
     * @param RouteInterface $route
     * @param array $args
     * @param bool $isAbsolute
     *
     * @return string
     */
    public function getRouteUrl(RouteInterface $route, array $args = [], bool $isAbsolute = false): ?string;

    /**
     * Déclaration d'un groupe.
     *
     * @param string $prefix
     * @param callable $group
     *
     * @return RouteGroupInterface
     */
    public function group(string $prefix, callable $group): RouteGroupInterface;

    /**
     * Traitement de la requête.
     *
     * @param RequestInterface $request
     *
     * @return ResponseInterface
     */
    public function handleRequest(RequestInterface $request): ResponseInterface;

    /**
     * Déclaration d'une route.
     *
     * @param string $method
     * @param string $path
     * @param string|callable $handler
     *
     * @return RouteInterface
     */
    public function map(string $method, string $path, $handler): RouteInterface;

    /**
     * Expédition de la réponse
     *
     * @param ResponseInterface $response
     *
     * @return bool
     */
    public function sendResponse(ResponseInterface $response): bool;

    /**
     * Définition du préfixe de base des chemins de route.
     *
     * @param string $basePrefix
     *
     * @return static
     */
    public function setBasePrefix(string $basePrefix): RouterInterface;

    /**
     * Définition de la route courante.
     *
     * @param RouteInterface $route
     *
     * @return RouterInterface
     */
    public function setCurrentRoute(RouteInterface $route): RouterInterface;

    /**
     * Termine le cycle de la requête et de la réponse HTTP.
     *
     * @param RequestInterface $request
     * @param ResponseInterface $response
     *
     * @return void
     */
    public function terminateEvent(RequestInterface $request, ResponseInterface $response): void;
}