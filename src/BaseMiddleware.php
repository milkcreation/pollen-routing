<?php

declare(strict_types=1);

namespace Pollen\Routing;

use Psr\Http\Message\ResponseInterface as PsrResponse;
use Psr\Http\Message\ServerRequestInterface as PsrRequest;
use Psr\Http\Server\RequestHandlerInterface;

abstract class BaseMiddleware implements MiddlewareInterface
{
    /**
     * @inheritDoc
     */
    public function process(PsrRequest $request, RequestHandlerInterface $handler): PsrResponse
    {
        return $handler->handle($request);
    }

    /**
     * @inheritDoc
     */
    public function beforeSend(PsrResponse $response, RouterInterface $router): PsrResponse
    {
        return $router->beforeSendResponse($response);
    }
}