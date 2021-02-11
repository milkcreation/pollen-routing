<?php

declare(strict_types=1);

namespace Pollen\Routing\Concerns;

use League\Route\Route as BaseRoute;

trait RouteCollectionTrait
{
    /**
     * @inheritDoc
     *
     * @return static
     */
    public function map(string $method, string $path, $handler): BaseRoute
    {
        return parent::map($method, $path, $handler);
    }

    /**
     * {@inheritDoc}
     *
     * @return static
     */
    public function get($path, $handler): BaseRoute
    {
        return parent::get($path, $handler);
    }

    /**
     * {@inheritDoc}
     *
     * @return static
     */
    public function post($path, $handler): BaseRoute
    {
        return parent::post($path, $handler);
    }

    /**
     * @inheritDoc
     *
     * @return static
     */
    public function put($path, $handler): BaseRoute
    {
        return parent::put($path, $handler);
    }

    /**
     * @inheritDoc
     *
     * @return static
     */
    public function patch($path, $handler): BaseRoute
    {
        return parent::patch($path, $handler);
    }

    /**
     * @inheritDoc
     *
     * @return static
     */
    public function delete($path, $handler): BaseRoute
    {
        return parent::delete($path, $handler);
    }

    /**
     * @inheritDoc
     *
     * @return static
     */
    public function head($path, $handler): BaseRoute
    {
        return parent::head($path, $handler);
    }

    /**
     * @inheritDoc
     *
     * @return static
     */
    public function options($path, $handler): BaseRoute
    {
        return parent::options($path, $handler);
    }
}