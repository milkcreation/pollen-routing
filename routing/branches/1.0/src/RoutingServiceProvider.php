<?php

declare(strict_types=1);

namespace Pollen\Routing;

use Pollen\Container\BaseServiceProvider;
use Pollen\Routing\Middleware\XhrMiddleware;
use Pollen\Routing\Strategy\ApplicationStrategy;
use Pollen\Routing\Strategy\JsonStrategy;
use Laminas\Diactoros\ResponseFactory;

class RoutingServiceProvider extends BaseServiceProvider
{
    protected $provides = [
        RouterInterface::class,
        'routing.middleware.xhr',
        'routing.strategy.app',
        'routing.strategy.json'
    ];

    /**
     * @inheritDoc
     */
    public function register(): void
    {
        $this->getContainer()->share(RouterInterface::class, function () {
            return new Router([], $this->getContainer());
        });
        $this->registerMiddlewares();
        $this->registerStrategies();

    }

    /**
     * Déclaration des middlewares.
     *
     * @return void
     */
    public function registerMiddlewares(): void
    {
        $this->getContainer()->add('routing.middleware.xhr', function () {
            return new XhrMiddleware();
        });
    }

    /**
     * Déclaration des stratégies.
     *
     * @return void
     */
    public function registerStrategies(): void
    {
        $this->getContainer()->add('routing.strategy.app', function () {
            $applicationStrategy = new ApplicationStrategy();
            $applicationStrategy->setContainer($this->getContainer());

            return $applicationStrategy;
        });
        $this->getContainer()->add('routing.strategy.json', function () {
            $jsonStrategy = new JsonStrategy(new ResponseFactory());
            $jsonStrategy->setContainer($this->getContainer());

            return $jsonStrategy;
        });
    }
}