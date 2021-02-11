<?php

declare(strict_types=1);

namespace Pollen\Routing\Strategy;

use Pollen\Http\Response;
use Pollen\Http\ResponseInterface;
use League\Route\Strategy\ApplicationStrategy as BaseApplicationStrategy;
use League\Route\Route;
use Psr\Http\Message\ResponseInterface as PsrResponse;
use Psr\Http\Message\ServerRequestInterface as PsrRequest;

class ApplicationStrategy extends BaseApplicationStrategy
{
    /**
     * @param Route $route
     * @param PsrRequest $request
     *
     * @return PsrResponse
     */
    public function invokeRouteCallable(Route $route, PsrRequest $request): PsrResponse
    {
        $controller = $route->getCallable($this->getContainer());

        $args = array_values($route->getVars());
        $args[] = $request;
        $response = $controller(...$args);

        if ($response instanceof ResponseInterface) {
            $response = $response->psr();
        } elseif (!$response instanceof PsrResponse) {
            $response = is_string($response) ? (new Response($response))->psr() : (new Response())->psr();
        }
        return $this->decorateResponse($response);
    }
}
