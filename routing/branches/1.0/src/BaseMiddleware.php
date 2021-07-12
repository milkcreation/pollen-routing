<?php

declare(strict_types=1);

namespace Pollen\Routing;

use Pollen\Http\JsonResponse;
use Pollen\Http\RedirectResponse;
use Pollen\Support\Proxy\HttpRequestProxy;
use Pollen\Support\Proxy\RouterProxy;
use Psr\Http\Message\ResponseInterface as PsrResponse;
use Psr\Http\Message\ServerRequestInterface as PsrRequest;
use Psr\Http\Server\RequestHandlerInterface;

abstract class BaseMiddleware implements MiddlewareInterface
{
    use HttpRequestProxy;
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

    /**
     * Returns a JsonResponse of json serialized data.
     *
     * @param string|array|object|null $data
     * @param int $status
     * @param array $headers
     *
     * @return PsrResponse
     */
    protected function json($data = null, int $status = 200, array $headers = []): PsrResponse
    {
        return (new JsonResponse($data, $status, $headers))->psr();
    }

    /**
     * Returns a RedirectResponse for an absolute url or a relative path.
     *
     * @param string $path
     * @param int $status
     * @param array $headers
     *
     * @return PsrResponse
     */
    protected function redirect(string $path = '/', int $status = 302, array $headers = []): PsrResponse
    {
        return (new RedirectResponse($path, $status, $headers))->psr();
    }

    /**
     * Returns a RedirectResponse to the request referer.
     *
     * @param int $status Statut de redirection.
     * @param array $headers Liste des entêtes complémentaires associées à la redirection.
     *
     * @return PsrResponse
     */
    protected function referer(int $status = 302, array $headers = []): PsrResponse
    {
        return $this->redirect($this->httpRequest()->headers->get('referer'), $status, $headers);
    }

    /**
     * Returns a RedirectResponse for a named route.
     *
     * @param string $name
     * @param array $params
     * @param bool $isAbsolute
     * @param int $status
     * @param array $headers
     *
     * @return PsrResponse
     */
    protected function route(
        string $name,
        array $params = [],
        bool $isAbsolute = false,
        int $status = 302,
        array $headers = []
    ): PsrResponse {
        return $this->router()->getNamedRouteRedirect($name, $params, $isAbsolute, $status, $headers)->psr();
    }
}