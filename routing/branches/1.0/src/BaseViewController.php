<?php

declare(strict_types=1);

namespace Pollen\Routing;

use Pollen\Http\ResponseInterface;
use Pollen\View\ViewEngine;
use Pollen\View\ViewEngineInterface;
use RuntimeException;

abstract class BaseViewController extends BaseController
{
    /**
     * Instance du moteur de gabarits d'affichage.
     * @var
     */
    protected $viewEngine;

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
            $this->viewEngine = new ViewEngine();
            if ($container = $this->getContainer()) {
                $this->viewEngine->setContainer($container);
            }

            $this->viewEngine->setDirectory($dir);
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
     * Génération de la réponse HTTP associée à l'affichage d'un gabarit.
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