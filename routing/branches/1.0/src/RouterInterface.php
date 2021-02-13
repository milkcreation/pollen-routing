<?php

declare(strict_types=1);

namespace Pollen\Routing;

use Pollen\Http\RequestInterface;
use Pollen\Http\ResponseInterface;

/**
 * @mixin \Pollen\Routing\Concerns\RouteCollectionTrait
 * @mixin \Pollen\Support\Concerns\ContainerAwareTrait
 */
interface RouterInterface
{
    /**
     * Récupération de l'instance courante.
     *
     * @return static
     */
    public static function instance(): RouterInterface;

    /**
     * Récupération du préfixe de base des chemins de route.
     *
     * @return string
     */
    public function getBasePrefix(): string;

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
}