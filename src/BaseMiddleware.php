<?php

declare(strict_types=1);

namespace Pollen\Routing;

use Pollen\Support\Proxy\RouterProxy;
use Psr\Http\Message\ResponseInterface as PsrResponse;
use Psr\Http\Message\ServerRequestInterface as PsrRequest;
use Psr\Http\Server\RequestHandlerInterface;

abstract class BaseMiddleware implements MiddlewareInterface
{
    use RouterProxy;

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