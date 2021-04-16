<?php

declare(strict_types=1);

namespace Pollen\Routing;

use Pollen\Http\BinaryFileResponse;
use Pollen\Http\BinaryFileResponseInterface;
use Pollen\Http\JsonResponse;
use Pollen\Http\JsonResponseInterface;
use Pollen\Http\RedirectResponse;
use Pollen\Http\RedirectResponseInterface;
use Pollen\Http\Response;
use Pollen\Http\ResponseInterface;
use Pollen\Support\Concerns\ParamsBagAwareTrait;
use Pollen\Support\Env;
use Pollen\Support\Proxy\ContainerProxy;
use Pollen\Support\Proxy\HttpRequestProxy;
use Pollen\Support\Proxy\RouterProxy;
use Psr\Container\ContainerInterface as Container;
use SplFileInfo;

abstract class BaseController
{
    use ContainerProxy;
    use ParamsBagAwareTrait;
    use HttpRequestProxy;
    use RouterProxy;

    /**
     * Indicateur d'activation du mode de débogage.
     * @var bool|null
     */
    protected $debug;

    /**
     * Instance du moteur de gabarits d'affichage.
     * @var
     */
    protected $viewEngine;

    /**
     * @param Container|null $container
     */
    public function __construct(?Container $container = null)
    {
        if ($container !== null) {
            $this->setContainer($container);
        }
        $this->boot();
    }

    /**
     * Initialisation du controleur.
     *
     * @return void
     */
    public function boot(): void { }

    /**
     * Vérification d'activation du mode de débogage.
     *
     * @return bool
     */
    protected function debug(): bool
    {
        return is_null($this->debug) ? Env::isDev() : $this->debug;
    }

    /**
     * Retourne la réponse de téléchargement ou d'affichage d'un fichier.
     *
     * @param SplFileInfo|string $file
     * @param string|null $fileName
     * @param string $disposition attachment|inline
     *
     * @return BinaryFileResponseInterface
     */
    protected function file(
        $file,
        string $fileName = null,
        string $disposition = 'attachment'
    ): BinaryFileResponseInterface {
        $response = new BinaryFileResponse($file);

        $filename = $fileName ?? $response->getFile()->getFilename();
        $response->headers->set ('Content-Type', $response->getFile()->getMimeType());
        $response->setContentDisposition($disposition, $filename);

        return $response;
    }

    /**
     * Retourne la réponse JSON HTTP.
     *
     * @param string|array|object|null $data
     * @param int $status
     * @param array $headers
     *
     * @return JsonResponseInterface
     */
    protected function json($data = null, int $status = 200, array $headers = []): JsonResponseInterface
    {
        return new JsonResponse($data, $status, $headers);
    }

    /**
     * Récupération de l'instance du gestionnaire de redirection|Redirection vers un chemin.
     *
     * @param string $path url absolue|relative de redirection.
     * @param int $status Statut de redirection.
     * @param array $headers Liste des entêtes complémentaires associées à la redirection.
     *
     * @return RedirectResponseInterface
     */
    protected function redirect(string $path = '/', int $status = 302, array $headers = []): RedirectResponseInterface
    {
        return new RedirectResponse($path, $status, $headers);
    }

    /**
     * Redirection vers la page d'origine.
     *
     * @param int $status Statut de redirection.
     * @param array $headers Liste des entêtes complémentaires associées à la redirection.
     *
     * @return RedirectResponseInterface
     */
    protected function referer(int $status = 302, array $headers = []): RedirectResponseInterface
    {
        return $this->redirect($this->httpRequest()->headers->get('referer'), $status, $headers);
    }

    /**
     * Retourne la réponse HTTP.
     *
     * @param string $content .
     * @param int $status
     * @param array $headers
     *
     * @return ResponseInterface
     */
    protected function response($content = '', int $status = 200, array $headers = []): ResponseInterface
    {
        return new Response($content, $status, $headers);
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
     * @return RedirectResponseInterface
     */
    protected function route(
        string $name,
        array $params = [],
        bool $isAbsolute = false,
        int $status = 302,
        array $headers = []
    ): RedirectResponseInterface {
        return $this->router()->getNamedRouteRedirect($name, $params, $isAbsolute, $status, $headers);
    }

    /**
     * Définition de l'activation du mode de débogage.
     *
     * @param bool $debug
     *
     * @return static
     */
    public function setDebug(bool $debug = true): self
    {
        $this->debug = $debug;

        return $this;
    }
}
