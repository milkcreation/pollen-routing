<?php

declare(strict_types=1);

namespace Pollen\Routing;

use Pollen\Support\Concerns\ContainerAwareTrait;
use Pollen\Http\RedirectResponse;
use Pollen\Http\RedirectResponseInterface;
use Pollen\Http\Request;
use Pollen\Http\Response;
use Pollen\Http\ResponseInterface;
use Pollen\Support\Concerns\ParamsBagTrait;
use Pollen\Support\Env;
use Pollen\View\ViewEngine;
use Pollen\View\ViewEngineInterface;
use Psr\Container\ContainerInterface as Container;
use RuntimeException;

class BaseController
{
    use ContainerAwareTrait;
    use ParamsBagTrait;

    /**
     * Instance du moteur de gabarits d'affichage.
     * @var
     */
    protected $viewEngine;

    /**
     * Indicateur d'activation du mode de déboguage.
     * @var bool|null
     */
    protected $debug;

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
    public function boot(): void {}

    /**
     * Vérification d'activation du mode de deboguage.
     *
     * @return bool
     */
    protected function debug(): bool
    {
        return is_null($this->debug) ? Env::isDev() : $this->debug;
    }

    /**
     * Vérification d'existance d'un gabarit d'affichage.
     *
     * @param string $view Nom de qualification du gabarit.
     *
     * @return bool
     */
    public function hasView(string $view): bool
    {
        return $this->viewEngine()->exists($view);
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
    public function redirect(string $path = '/', int $status = 302, array $headers = []): RedirectResponseInterface
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
    public function referer(int $status = 302, array $headers = []): RedirectResponseInterface
    {
        return $this->redirect(Request::getFromGlobals()->headers->get('referer'), $status, $headers);
    }

    /**
     * Récupération de l'affichage d'un gabarit.
     *
     * @param string $view Nom de qualification du gabarit.
     * @param array $data Liste des variables passées en argument.
     *
     * @return string
     */
    public function render(string $view, array $data = []): string
    {
        return $this->viewEngine()->render($view, $data);
    }

    /**
     * Récupération de la reponse HTTP.
     *
     * @param string $content.
     * @param int $status
     * @param array $headers
     *
     * @return ResponseInterface
     */
    public function response($content = '', int $status = 200, array $headers = []): ResponseInterface
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
     * /
    public function route(string $name, array $params = [], int $status = 302, array $headers = []): RedirectResponse
    {
        return Redirect::route($name, $params, $status, $headers);
    }
    /**/

    /**
     * Définition de l'activation du mode de deboguage.
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
            $this->viewEngine()->share($k, $v);
        }

        return $this;
    }

    /**
     * Génération de la reponse HTTP associé à l'affichage d'un gabarit.
     *
     * @param string $view Nom de qualification du gabarit.
     * @param array $data Liste des variables passées en argument.
     *
     * @return ResponseInterface
     */
    public function view(string $view, array $data = []): ResponseInterface
    {
        return $this->response($this->render($view, $data));
    }

    /**
     * Moteur d'affichage des gabarits d'affichage.
     *
     * @return ViewEngineInterface
     */
    public function viewEngine(): ViewEngineInterface
    {
        if ($this->viewEngine === null) {
            if ((!$dir = $this->viewEngineDirectory()) || !is_dir($dir)) {
                throw new RuntimeException(sprintf(
                    'View Engine Directory unavailable in HttpController [%s]',
                    get_class($this)
                ));
            }
            $this->viewEngine = new ViewEngine($dir);
        }
        return $this->viewEngine;
    }

    /**
     * Répertoire des gabarits d'affichage.
     *
     * @return string
     */
    public function viewEngineDirectory(): string
    {
        return get_template_directory();
    }
}