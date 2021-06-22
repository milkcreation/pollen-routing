<?php

declare(strict_types=1);

namespace Pollen\Routing;

use Pollen\Http\ResponseInterface;
use Pollen\View\Engines\Plates\PlatesViewEngine;
use Pollen\View\View;
use Pollen\View\ViewInterface;
use RuntimeException;

abstract class BaseViewController extends BaseController
{
    /**
     * Instance du moteur de gabarits d'affichage.
     */
    protected ?ViewInterface $view = null;

    /**
     * Moteur d'affichage des gabarits d'affichage.
     *
     * @return ViewInterface
     */
    protected function getView(): ViewInterface
    {
        if ($this->view === null) {
            if ((!$directory = $this->viewDirectory()) || !is_dir($directory)) {
                throw new RuntimeException(
                    sprintf(
                        'View Engine Directory unavailable in HttpController [%s]',
                        get_class($this)
                    )
                );
            }

            $this->view = View::createFromPlates(
                function (PlatesViewEngine $platesViewEngine) use ($directory) {
                    $platesViewEngine->setDirectory($directory);

                    if ($container = $this->getContainer()) {
                        $this->viewEngine->setContainer($container);
                    }

                    return $platesViewEngine;
                }
            );
        }

        return $this->view;
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
        return $this->getView()->getEngine()->exists($view);
    }

    /**
     * Récupération de l'affichage d'un gabarit.
     *
     * @param string $view Nom de qualification du gabarit.
     * @param array $datas Liste des variables passées en argument.
     *
     * @return string
     */
    protected function render(string $view, array $datas = []): string
    {
        return $this->getView()->render($view, $this->datas($datas)->all());
    }

    /**
     * Définition du moteur des gabarits d'affichage.
     *
     * @param ViewInterface $view
     *
     * @return static
     */
    public function setView(ViewInterface $view): self
    {
        $this->view = $view;

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
            $this->getView()->getEngine()->share($k, $v);
        }

        return $this;
    }

    /**
     * Génération de la réponse HTTP associée à l'affichage d'un gabarit.
     *
     * @param string $view Nom de qualification du gabarit.
     * @param array $datas Liste des variables passées en argument.
     *
     * @return ResponseInterface
     */
    protected function view(string $view, array $datas = []): ResponseInterface
    {
        return $this->response($this->render($view, $datas));
    }

    /**
     * Répertoire des gabarits d'affichage.
     *
     * @return string
     */
    abstract protected function viewDirectory(): string;
}