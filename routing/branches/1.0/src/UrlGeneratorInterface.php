<?php

declare(strict_types=1);

namespace Pollen\Routing;

use Pollen\Support\Proxy\HttpRequestProxyInterface;

interface UrlGeneratorInterface extends HttpRequestProxyInterface
{
    /**
     * Get url.
     *
     * @param array $args
     *
     * @return string
     */
    public function get(array $args = []): string;

    /**
     * Set if output url format is absolute.
     *
     * @var bool $isAbsolute
     *
     * @return static
     */
    public function setAbsoluteEnabled(bool $isAbsolute = false): UrlGeneratorInterface;

    /**
     * Set url base prefix.
     *
     * @var string|null $basePrefix
     *
     * @return static
     */
    public function setBasePrefix(?string $basePrefix = null): UrlGeneratorInterface;

    /**
     * Set url host.
     *
     * @var string|null $host
     *
     * @return static
     */
    public function setHost(?string $host = null): UrlGeneratorInterface;

    /**
     * Set url port.
     *
     * @var int|null $port
     *
     * @return static
     */
    public function setPort(?int $port = null): UrlGeneratorInterface;

    /**
     * Set url scheme.
     *
     * @param string|null $scheme
     *
     * @return static
     */
    public function setScheme(?string $scheme = null): UrlGeneratorInterface;

    /**
     * Set url patterns.
     *
     * @param array $urlPatterns
     *
     * @return static
     */
    public function setUrlPatterns(array $urlPatterns): UrlGeneratorInterface;
}