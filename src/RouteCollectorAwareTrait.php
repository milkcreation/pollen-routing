<?php

declare(strict_types=1);

namespace Pollen\Routing;

use League\Route\Route;
use InvalidArgumentException;
use RuntimeException;

trait RouteCollectorAwareTrait
{
    /**
     * Affectation d'un middleware selon un alias de qualification dans le conteneur d'injection de dépendances.
     *
     * @param string $alias
     *
     * @return static
     */
    public function middle(string $alias): RouteCollectorAwareTrait
    {
        if (!$this->getContainer()) {
            throw new RuntimeException('Middleware aliased declaration require dependency injection container');
        }

        if (!$this->getContainer()->has("routing.middleware.{$alias}")) {
            throw new InvalidArgumentException(
                sprintf('Middleware alias [%s] is not being managed by the container', $alias)
            );
        }

        $this->lazyMiddleware("routing.middleware.{$alias}");

        return $this;
    }

    /**
     * Affectation d'une stratégie selon un alias de qualification dans le conteneur d'injection de dépendances.
     *
     * @param string $alias
     *
     * @return static
     */
    public function strategy(string $alias): RouteCollectorAwareTrait
    {
        if (!$this->getContainer()) {
            throw new RuntimeException('Strategy aliased declaration require dependency injection container');
        }

        if (!$this->getContainer()->has("routing.strategy.{$alias}")) {
            throw new InvalidArgumentException(
                sprintf('Strategy alias (%s) is not being managed by the container', $alias)
            );
        }

        $this->setStrategy($this->getContainer()->get("routing.strategy.{$alias}"));

        return $this;
    }

    /**
     * Définition d'une route pour la méthode de requête HTTP DELETE.
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
     * Définition d'une route pour la méthode de requête HTTP GET.
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
     * Définition d'une route pour la méthode de requête HTTP OPTIONS.
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
     * Définition d'une route pour la méthode de requête HTTP PATCH.
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
     * Définition d'une route pour la méthode de requête HTTP POST.
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
     * Définition d'une route pour la méthode de requête HTTP PUT.
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
     * Déclaration d'une route dédiée aux requêtes Ajax XmlHttpRequest (Xhr).
     *
     * @param string $path Chemin relatif vers la route.
     * @param string|callable $handler Traitement de la route.
     * @param string $method Méthode de la requête.
     *
     * @return RouteInterface
     */
    public function xhr(string $path, $handler, string $method = 'POST'): RouteInterface
    {
        return $this->map($method, $path, $handler)->middle('xhr');
    }
}