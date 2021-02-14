<?php

declare(strict_types=1);

namespace Pollen\Routing\Strategy;

use Exception;
use Pollen\Http\Response;
use Pollen\Http\ResponseInterface;
use League\Route\Strategy\JsonStrategy as BaseJsonStrategy;
use League\Route\Route;
use Psr\Http\Message\ResponseInterface as PsrResponse;
use Psr\Http\Message\ServerRequestInterface as PsrRequest;

class JsonStrategy extends BaseJsonStrategy
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
        $response = $controller(...$args);

        if ($response instanceof ResponseInterface) {
            $response = $response->psr();
        } elseif (!$response instanceof PsrResponse) {
            if ($this->isJsonSerializable($response)) {
                try {
                    $response = (new Response(json_encode($response, JSON_THROW_ON_ERROR)))->psr();
                } catch (Exception $e) {
                    (new Response($e->getMessage(), 500))->psr();
                }
            } else {
                $response = (new Response('', 404))->psr();
            }
        }

        return $this->decorateResponse($response);
    }
}