<?php

declare(strict_types=1);

namespace Pollen\Routing\Middleware;

use Pollen\Http\JsonResponse;
use Pollen\Routing\BaseMiddleware;
use Psr\Http\Message\ResponseInterface as PsrResponse;
use Psr\Http\Message\ServerRequestInterface as PsrRequest;
use Psr\Http\Server\RequestHandlerInterface;

class XhrMiddleware extends BaseMiddleware
{
    /**
     * @inheritDoc
     */
    public function process(PsrRequest $request, RequestHandlerInterface $handler): PsrResponse
    {
        if ('XMLHttpRequest' === $request->getHeaderLine('X-Requested-With')) {
            return $handler->handle($request);
        }

        return (new JsonResponse(
            [
                'status_code'   => 500,
                'reason_phrase' => 'Only XMLHttpRequest (XHR) are allowed',
            ], 500
        ))->psr();
    }
}