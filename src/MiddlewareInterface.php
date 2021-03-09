<?php

declare(strict_types=1);

namespace Pollen\Routing;

use Pollen\Support\Proxy\RouterProxyInterface;
use Psr\Http\Message\ResponseInterface as PsrResponse;
use Psr\Http\Server\MiddlewareInterface as BaseMiddlewareInterface;

interface MiddlewareInterface extends BaseMiddlewareInterface, RouterProxyInterface
{
    /**
     * Pré-traitement de la réponse HTTP avant son envoi.
     *
     * @param PsrResponse $response
     * @param RouterInterface $router
     *
     * @return PsrResponse
     */
    public function beforeSend(PsrResponse $response, RouterInterface $router): PsrResponse;
}