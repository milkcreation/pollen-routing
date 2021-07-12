<?php

declare(strict_types=1);

namespace Pollen\Routing;

use FastRoute\Dispatcher as FastRoute;
use Pollen\Http\RequestInterface;

class UrlMatcher implements UrlMatcherInterface
{
    /**
     * HTTP request instance.
     * @var RequestInterface
     */
    protected RequestInterface $request;

    /**
     * Router instance.
     * @var RouterInterface
     */
    protected RouterInterface $router;

    /**
     * @param RouterInterface $router
     * @param RequestInterface $request
     */
    public function __construct(RouterInterface $router, RequestInterface $request)
    {
        $this->router = clone $router;
        $this->request = $request;
    }

    /**
     * Vérification de correspondance
     * @return array
     */
    public function match(): array
    {
        $this->router->setHandleRequest($this->request);
        $this->router->getRouteCollector()->prepareRoutes($this->request->psr());

        $method = $this->request->getMethod();
        $uri    = $this->request->getRewriteBase() . $this->request->getPathInfo();

        $match = (new RouteDispatcher($this->router))->dispatch($method, $uri);

        if ($match[0] === FastRoute::FOUND) {
            $route = ($match[1] instanceof RouteInterface) ? $match[1] : new Route($method, $uri, $match[1]);
            $this->request->attributes->set('_route', $route);

            foreach((array)$match[2] as $varKey => $varVal) {
                $this->request->attributes->set("_$varKey", $varVal);
            }
        }

        return $match;
    }
}
