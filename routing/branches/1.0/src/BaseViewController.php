<?php

declare(strict_types=1);

namespace Pollen\Routing;

use Pollen\Http\ResponseInterface;
use Pollen\Support\ProxyResolver;
use Pollen\View\ViewInterface;
use Pollen\View\ViewManager;
use Pollen\View\ViewManagerInterface;
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
            try {
                $manager = ViewManager::getInstance();
            } catch (RuntimeException $e) {
                $manager = ProxyResolver::getInstance(
                    ViewManagerInterface::class,
                    ViewManager::class,
                    method_exists($this, 'getContainer') ? $this->getContainer() : null
                );
            }
            $this->view = $manager->getDefaultView();
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
}