<?php

declare(strict_types=1);

namespace Pollen\Routing;

use FastRoute\RouteCollector;
use Pollen\Http\Request;
use Pollen\Routing\Concerns\RouteCollectionTrait;
use Pollen\Routing\Strategy\ApplicationStrategy;
use Pollen\Support\Concerns\ContainerAwareTrait;
use Exception;
use League\Route\RouteGroup as BaseRouteGroup;
use League\Route\Router as BaseRouter;
use League\Route\Route as BaseRoute;
use Laminas\HttpHandlerRunner\Emitter\SapiEmitter;
use Psr\Container\ContainerInterface as Container;
use Psr\Http\Message\ResponseInterface as PsrResponse;
use Psr\Http\Message\ServerRequestInterface as PsrRequest;

class Router extends BaseRouter implements RouterInterface
{
    use ContainerAwareTrait;
    use RouteCollectionTrait;

    /**
     * @var string|null
     */
    private $basePrefixNormalized;

    /**
     * @var string
     */
    protected $basePrefix = '';

    /**
     * @param RouteCollector|null $routeCollector
     * @param Container|null $container
     */
    public function __construct(?RouteCollector $routeCollector = null, ?Container $container = null
    ) {
        if ($container !== null) {
            $this->setContainer($container);
        }

        parent::__construct($routeCollector);

        $this->setBasePrefix(Request::getFromGlobals()->getRewriteBase());

        add_action(
            'parse_request',
            function () {
                $request = Request::getFromGlobals();

                try {
                    $response = $this->dispatch($request->psr());

                    if ($response->getStatusCode() !== 100) {
                        $this->send($response);
                        exit;
                    }
                } catch (Exception $e) {
                    /* * /
                    if (wp_using_themes() && $request->isMethod('GET')) {
                        if (config('routing.remove_trailing_slash', true)) {
                            $permalinks = get_option('permalink_structure');
                            if (substr($permalinks, -1) == '/') {
                                update_option('permalink_structure', rtrim($permalinks, '/'));
                            }

                            $path = Request::getBaseUrl() . Request::getPathInfo();

                            if (($path != '/') && (substr($path, -1) == '/')) {
                                $dispatcher = new Dispatcher($this->manager->getData());
                                $match = $dispatcher->dispatch($method, rtrim($path, '/'));

                                if ($match[0] === FastRoute::FOUND) {
                                    $redirect_url = rtrim($path, '/');
                                    $redirect_url .= ($qs = Request::getQueryString()) ? "?{$qs}" : '';

                                    $response = HttpRedirect::createPsr($redirect_url);
                                    $this->manager->emit($response);
                                    exit;
                                }
                            }
                        }
                    }
                    /**/
                }
            },
            0
        );
    }

    /**
     * @inheritDoc
     */
    public function dispatch(PsrRequest $request): PsrResponse
    {
        if ($this->getStrategy() === null) {
            $this->setStrategy(new ApplicationStrategy);
        }
        return parent::dispatch($request);
    }

    /**
     * @inheritDoc
     */
    public function getBasePrefix(): string
    {
        if ($this->basePrefixNormalized === null) {
            $this->basePrefixNormalized = $this->basePrefix ? '/' . rtrim(ltrim($this->basePrefix, '/'), '/') : '';
        }
        return $this->basePrefixNormalized;
    }

    /**
     * {@inheritDoc}
     *
     * @return RouteGroup
     */
    public function group(string $prefix, callable $group): BaseRouteGroup
    {
        $group = new RouteGroup($prefix, $group, $this);

        if ($container = $this->getContainer()) {
            $group->setContainer($container);
        }

        $this->groups[] = $group;

        return $group;
    }

    /**
     * {@inheritDoc}
     *
     * @return Route
     */
    public function map(string $method, string $path, $handler): BaseRoute
    {
        $path = $this->getBasePrefix() . sprintf('/%s', ltrim($path, '/'));
        $route = new Route($method, $path, $handler);

        if ($container = $this->getContainer()) {
            $route->setContainer($container);
        }

        $this->routes[] = $route;

        return $route;
    }

    /**
     * @param PsrResponse $response
     *
     * @return bool
     */
    public function send(PsrResponse $response): bool
    {
        return (new SapiEmitter())->emit($response);
    }

    /**
     * @inheritDoc
     */
    public function setBasePrefix(string $basePrefix): RouterInterface
    {
        $this->basePrefix = $basePrefix;

        return $this;
    }
}