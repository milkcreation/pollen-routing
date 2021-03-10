<?php

declare(strict_types=1);

namespace Pollen\Routing;

use FastRoute\BadRouteException;
use FastRoute\RouteParser\Std as RouteParser;
use Pollen\Http\RequestInterface;
use Pollen\Http\UrlManipulator;
use Pollen\Support\Proxy\HttpRequestProxy;
use LogicException;

class UrlGenerator implements UrlGeneratorInterface
{
    use HttpRequestProxy;

    /**
     * @var string|null
     */
    protected $basePrefix;

    /**
     * @var string|null
     */
    protected $host;

    /**
     * @var bool
     */
    protected $isAbsolute = false;

    /**
     * @var string
     */
    protected $path = '';

    /**
     * @var int
     */
    protected $port;

    /**
     * @var string|null http|https
     */
    protected $scheme;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var RouteCollector
     */
    protected $routeCollector;

    /**
     * @var RouterInterface
     */
    protected $router;

    /**
     * @var array
     */
    protected $urlPatterns = [
        '/{(.+?):number}/'        => '{$1:[0-9]+}',
        '/{(.+?):word}/'          => '{$1:[a-zA-Z]+}',
        '/{(.+?):alphanum_dash}/' => '{$1:[a-zA-Z0-9-_]+}',
        '/{(.+?):slug}/'          => '{$1:[a-z0-9-]+}',
        '/{(.+?):uuid}/'          => '{$1:[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}+}'
    ];

    /**
     * @param string $path
     * @param RequestInterface|null $request
     */
    public function __construct(string $path, ?RequestInterface $request)
    {
        $this->path = $path;

        if ($request !== null) {
            $this->setHttpRequest($request);
        }
    }

    /**
     * @inheritDoc
     */
    public function get(array $args = []): string
    {
        try {
            $patterns = (new RouteParser())->parse($this->parseRoutePath($this->path));
            $patterns = array_reverse($patterns);

            $segments = null;
            $throw = null;
            $queryArgs = null;

            foreach ($patterns as $parts) {
                $i = 0;
                $params = $args;
                $segments = [];

                foreach ($parts as $matches) {
                    if (!is_array($matches) || count($matches) !== 2) {
                        $segments[] = $matches;
                        continue;
                    }
                    [$key, $regex] = $matches;

                    if (isset($params[$key])) {
                        $segment = $params[$key];
                        unset($params[$key]);
                    } elseif (isset($params[$i])) {
                        $segment = $params[$i];
                        unset($params[$i]);
                        $i++;
                    } else {
                        $throw = new LogicException(
                            'Invalid Route Url: Insufficient number of arguments provided'
                        );
                        $segments = null;
                        break;
                    }

                    if (!preg_match("#{$regex}+#", (string)$segment)) {
                        $throw = new LogicException(
                            'Invalid Route Url: Insufficient number of arguments provided'
                        );
                        $segments = null;
                        break;
                    }
                    $segments[] = $segment;
                }
                if (isset($segments)) {
                    $queryArgs = $params ?: [];
                    break;
                }
            }

            if (!isset($segments)) {
                throw $throw ?? new LogicException('Invalid Route Url');
            }

            $url = ($this->basePrefix ? sprintf('/%s', rtrim(ltrim($this->basePrefix))) : ''). implode('', $segments);
            if ($queryArgs) {
                $url = (string)(new UrlManipulator($url))->with($queryArgs);
            }

            if (!$this->isAbsolute) {
                return $url;
            }

            $host = $this->host ?? $this->httpRequest()->getHost();
            $port = $this->port ?? $this->httpRequest()->getPort();
            $scheme = $this->scheme ?? $this->httpRequest()->getScheme();

            if ((($port === 80) && ($scheme = 'http')) || (($port === 443) && ($scheme = 'https'))) {
                $port = '';
            }

            return $scheme . '://' . $host . ($port ? ':' . $port : '') . $url;
        } catch (BadRouteException $e) {
            throw new LogicException(
                sprintf('Invalid Route Url: %s', $e->getMessage())
            );
        }
    }

    /**
     * Traitement du chemin suivant les motifs d'urls déclarés.
     *
     * @param string $path
     *
     * @return string
     */
    protected function parseRoutePath(string $path): string
    {
        $patterns = $this->urlPatterns;

        return preg_replace(array_keys($patterns), array_values($patterns), $path);
    }

    /**
     * @inheritDoc
     */
    public function setAbsoluteEnabled(bool $isAbsolute = false): UrlGeneratorInterface
    {
        $this->isAbsolute = $isAbsolute;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setBasePrefix(?string $basePrefix = null): UrlGeneratorInterface
    {
        $this->basePrefix = $basePrefix;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setHost(?string $host = null): UrlGeneratorInterface
    {
        $this->host = $host;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setPort(?int $port = null): UrlGeneratorInterface
    {
        $this->port = $port;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setScheme(?string $scheme = null): UrlGeneratorInterface
    {
        $this->scheme = $scheme;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setUrlPatterns(array $urlPatterns): UrlGeneratorInterface
    {
        $this->urlPatterns = $urlPatterns;

        return $this;
    }
}