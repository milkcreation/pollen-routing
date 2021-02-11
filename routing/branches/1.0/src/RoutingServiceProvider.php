<?php

declare(strict_types=1);

namespace Pollen\Routing;

use Pollen\Container\BaseServiceProvider;
use Pollen\Routing\Strategy\ApplicationStrategy;
use Pollen\Routing\Strategy\JsonStrategy;
use Pollen\Routing\Strategy\WpTemplateStrategy;
use Laminas\Diactoros\ResponseFactory;

class RoutingServiceProvider extends BaseServiceProvider
{
    protected $provides = [
        RouterInterface::class,
        'routing.strategy.app',
        'routing.strategy.json',
        'routing.strategy.wp-template',
    ];

    /**
     * @inheritDoc
     */
    public function register(): void
    {
        $this->getContainer()->share(RouterInterface::class, function () {
            return new Router(null, $this->getContainer());
        });
        $this->registerStrategies();
    }

    /**
     * Déclaration des stratégies.
     *
     * @return void
     */
    public function registerStrategies(): void
    {
        $this->getContainer()->add('routing.strategy.app', function () {
            return (new ApplicationStrategy())->setContainer($this->getContainer());
        });
        $this->getContainer()->add('routing.strategy.json', function () {
            return (new JsonStrategy(new ResponseFactory()))->setContainer($this->getContainer());
        });
        $this->getContainer()->add('routing.strategy.wp-template', function () {
            return (new WpTemplateStrategy($this->getContainer()->get(RouterInterface::class)))
                ->setContainer($this->getContainer());
        });
    }
}