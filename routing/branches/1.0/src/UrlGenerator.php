<?php

declare(strict_types=1);

namespace Pollen\Routing;

use FastRoute\BadRouteException;
use FastRoute\RouteParser\Std as RouteParser;
use Pollen\Http\Request;
use Pollen\Http\RequestInterface;
use Pollen\Http\UrlManipulation;
use LogicException;

class UrlGenerator
{
    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var RouteCollection
     */
    protected $routeCollection;

    /**
     * @var RouterInterface
     */
    protected $router;

    /**
     * @param RouterInterface $router
     * @param \Pollen\Http\RequestInterface|null $request
     */
    public function __construct(RouterInterface $router, ?RequestInterface $request)
    {
        $this->router = $router;
        $this->setRequest($request ?: Request::createFromGlobals());
    }

    /**
     * Récupération de l'url un chemin.
     *
     * @param string $path
     * @param array $args
     * @param bool $absolute
     * @param array $context
     *
     * @return string
     */
    public function getFromPath(string $path, array $args = [], bool $absolute = false, array $context = []): string
    {
        try {
            $patterns = (new RouteParser())->parse($this->parseRoutePath($path));
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

            $url = $this->router->getBasePrefix() . '/' . implode('/', $segments);
            if ($queryArgs) {
                $url = (string)(new UrlManipulation($url))->with($queryArgs);
            }

            if (!$absolute) {
                return $url;
            }

            $host = $context['host'] ?? $this->getRequest()->getHost();
            $port = $context['port'] ?? $this->getRequest()->getPort();
            $scheme = $context['scheme'] ?? $this->getRequest()->getScheme();

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
     * Récupération de l'url d'une route qualifiée.
     *
     * @param string $name
     * @param array $args
     * @param bool $absolute
     *
     * @return string
     */
    public function getFromNamedRoute(string $name, array $args = [], bool $absolute = false): ?string
    {
        if ($route = $this->router->getNamedRoute($name)) {
            return $this->getFromRoute($route, $args, $absolute);
        }
        return null;
    }

    /**
     * Récupération de l'url d'une route.
     *
     * @param RouteInterface $route
     * @param array $args
     * @param bool $absolute
     *
     * @return string
     */
    public function getFromRoute(RouteInterface $route, array $args = [], bool $absolute = false): string
    {
        return $this->getFromPath(
            $route->getPath(),
            $args,
            $absolute,
            [
                'host'   => $route->getHost(),
                'port'   => $route->getPort(),
                'scheme' => $route->getScheme(),
            ]
        );
    }

    /**
     * Récupération de la requête HTTP.
     *
     * @return RequestInterface
     */
    public function getRequest(): RequestInterface
    {
        return $this->request;
    }

    /**
     * Traitement du chemin suivant les motifs d'urls déclarés.
     *
     * @param string $path
     *
     * @return string
     */
    public function parseRoutePath(string $path): string
    {
        $patterns = $this->getRouteCollection()->getUrlPatterns();

        return preg_replace(array_keys($patterns), array_values($patterns), $path);
    }

    /**
     * Récupération de l'instance de collection de routes.
     *
     * @return RouteCollectionInterface
     */
    public function getRouteCollection(): RouteCollectionInterface
    {
        if ($this->routeCollection === null) {
            $this->routeCollection = $this->router->getRouteCollection();
        }

        return $this->routeCollection;
    }

    /**
     * Définition de la requête.
     *
     * @param RequestInterface $request
     *
     * @return static
     */
    public function setRequest(RequestInterface $request): self
    {
        $this->request = $request;

        return $this;
    }

    /**
     * Définition de l'instance de collection de routes.
     *
     * @param RouteCollectionInterface
     *
     * @return static
     */
    public function setRouteCollection(RouteCollectionInterface $routeCollection): self
    {
        $this->routeCollection = $routeCollection;

        return $this;
    }
}