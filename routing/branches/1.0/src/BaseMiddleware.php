<?php

declare(strict_types=1);

namespace Pollen\Routing;

use Pollen\Http\JsonResponse;
use Pollen\Http\JsonResponseInterface;
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
     * Retourne la réponse JSON HTTP.
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
     * Récupération de l'instance du gestionnaire de redirection|Redirection vers un chemin.
     *
     * @param string $path url absolue|relative de redirection.
     * @param int $status Statut de redirection.
     * @param array $headers Liste des entêtes complémentaires associées à la redirection.
     *
     * @return PsrResponse
     */
    protected function redirect(string $path = '/', int $status = 302, array $headers = []): PsrResponse
    {
        return (new RedirectResponse($path, $status, $headers))->psr();
    }

    /**
     * Redirection vers la page d'origine.
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
     * Redirection vers une route déclarée.
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