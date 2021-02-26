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
use Pollen\Support\Proxy\ContainerProxy;
use Pollen\Support\Proxy\HttpRequestProxy;
use Pollen\Support\Env;
use Pollen\View\ViewEngine;
use Pollen\View\ViewEngineInterface;
use Psr\Container\ContainerInterface as Container;
use RuntimeException;
use SplFileInfo;

abstract class BaseController
{
    use ContainerProxy;
    use ParamsBagAwareTrait;
    use HttpRequestProxy;

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
     * Moteur d'affichage des gabarits d'affichage.
     *
     * @return ViewEngineInterface
     */
    protected function getViewEngine(): ViewEngineInterface
    {
        if ($this->viewEngine === null) {
            if ((!$dir = $this->viewEngineDirectory()) || !is_dir($dir)) {
                throw new RuntimeException(
                    sprintf(
                        'View Engine Directory unavailable in HttpController [%s]',
                        get_class($this)
                    )
                );
            }
            $this->viewEngine = new ViewEngine($dir);
        }
        return $this->viewEngine;
    }

    /**
     * Vérification d'existence d'un gabarit d'affichage.
     *
     * @param string $view Nom de qualification du gabarit.
     *
     * @return bool
     */
    protected function hasView(string $view): bool
    {
        return $this->getViewEngine()->exists($view);
    }

    /**
     * Retourne la réponse de téléchargement ou d'affichage d'un fichier.
     *
     * @param SplFileInfo|string $file
     * @param string|null
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
        $response->setContentDisposition($disposition, $fileName ?? $response->getFile()->getFilename());

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
     * Récupération de l'affichage d'un gabarit.
     *
     * @param string $view Nom de qualification du gabarit.
     * @param array $data Liste des variables passées en argument.
     *
     * @return string
     */
    protected function render(string $view, array $data = []): string
    {
        return $this->getViewEngine()->render($view, $data);
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
     * @param string $name Nom de qualification de la route.
     * @param array $params Liste des paramètres de définition de l'url de la route.
     * @param int $status Statut de redirection.
     * @param array $headers Liste des entêtes complémentaires associées à la redirection.
     *
     * @return RedirectResponse
     */
    public function route(string $name, array $params = [], int $status = 302, array $headers = []): RedirectResponse
    {
        if ($this->containerHas(RouterInterface::class)) {
            /** @var RouterInterface $router */
            $router = $this->containerGet(RouterInterface::class);

            $url = $router->getNamedRouteUrl($name, $params);
            return new RedirectResponse($url, $status, $headers);
        }
        throw new RuntimeException('Any router are available');
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

    /**
     * Définition du moteur des gabarits d'affichage.
     *
     * @param ViewEngineInterface $viewEngine
     *
     * @return static
     */
    public function setViewEngine(ViewEngineInterface $viewEngine): self
    {
        $this->viewEngine = $viewEngine;

        return $this;
    }

    /**
     * Définition des variables partagées à l'ensemble des vues.
     *
     * @param string|array $key
     * @param mixed $value
     *
     * @return $this
     */
    public function share($key, $value = null): self
    {
        $keys = !is_array($key) ? [$key => $value] : $key;

        foreach ($keys as $k => $v) {
            $this->getViewEngine()->share($k, $v);
        }

        return $this;
    }

    /**
     * Génération de la réponse HTTP associé à l'affichage d'un gabarit.
     *
     * @param string $view Nom de qualification du gabarit.
     * @param array $data Liste des variables passées en argument.
     *
     * @return ResponseInterface
     */
    protected function view(string $view, array $data = []): ResponseInterface
    {
        return $this->response($this->render($view, $data));
    }

    /**
     * Répertoire des gabarits d'affichage.
     *
     * @return string
     */
    abstract protected function viewEngineDirectory(): string;
}