<?php

declare(strict_types=1);

namespace Pollen\Routing;

use Pollen\Support\Proxy\HttpRequestProxyInterface;

interface UrlGeneratorInterface extends HttpRequestProxyInterface
{
    /**
     * Récupération de l'url.
     *
     * @param array $args
     *
     * @return string
     */
    public function get(array $args = []): string;

    /**
     * Définition du format de sortie de l'url (absolue|relative)
     *
     * @var bool $isAbsolute
     *
     * @return static
     */
    public function setAbsoluteEnabled(bool $isAbsolute = false): UrlGeneratorInterface;

    /**
     * Définition du prefixe de base de l'url (REWRITE_BASE).
     *
     * @var string|null $basePrefix
     *
     * @return static
     */
    public function setBasePrefix(?string $basePrefix = null): UrlGeneratorInterface;

    /**
     * Définition de l'hôte (domaine) de l'url.
     *
     * @var string|null $host
     *
     * @return static
     */
    public function setHost(?string $host = null): UrlGeneratorInterface;

    /**
     * Définition du port de l'url.
     *
     * @var int|null $port
     *
     * @return static
     */
    public function setPort(?int $port = null): UrlGeneratorInterface;

    /**
     * Définition du protocole de l'url (http|https)
     *
     * @param string|null $scheme
     *
     * @return static
     */
    public function setScheme(?string $scheme = null): UrlGeneratorInterface;

    /**
     * Définition des motifs de substitution de l'url.
     *
     * @param array $urlPatterns
     *
     * @return static
     */
    public function setUrlPatterns(array $urlPatterns): UrlGeneratorInterface;
}