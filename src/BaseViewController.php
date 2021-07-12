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
     * View instance.
     * @var ViewInterface|null
     */
    protected ?ViewInterface $view = null;

    /**
     * Resolve view instance.
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
     * Checks if view template exists.
     *
     * @param string $view
     *
     * @return bool
     */
    protected function hasView(string $view): bool
    {
        return $this->getView()->getEngine()->exists($view);
    }

    /**
     * Get template render.
     *
     * @param string $view
     * @param array $datas
     *
     * @return string
     */
    protected function render(string $view, array $datas = []): string
    {
        return $this->getView()->render($view, ($d = $this->datas($datas)) ? $d->all() : []);
    }

    /**
     * Set view instance.
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
     * Shares datas in all view templates.
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
     * Returns a HTTP response for a view template.
     *
     * @param string $view
     * @param array $datas
     *
     * @return ResponseInterface
     */
    protected function view(string $view, array $datas = []): ResponseInterface
    {
        return $this->response($this->render($view, $datas));
    }
}