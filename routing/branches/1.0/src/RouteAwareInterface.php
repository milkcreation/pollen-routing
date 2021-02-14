<?php

declare(strict_types=1);

namespace Pollen\Routing;

use League\Route\Middleware\MiddlewareAwareInterface;
use League\Route\RouteConditionHandlerInterface;
use League\Route\Strategy\StrategyAwareInterface;
use League\Route\Strategy\StrategyInterface;
use Psr\Http\Server\MiddlewareInterface;

interface RouteAwareInterface extends
    MiddlewareAwareInterface,
    MiddlewareInterface,
    StrategyAwareInterface,
    RouteConditionHandlerInterface
{
    public function getHost(): ?string;
    public function getName(): ?string;
    public function getPort(): ?int;
    public function getScheme(): ?string;
    public function setHost(string $host): RouteConditionHandlerInterface;
    public function setName(string $name): RouteConditionHandlerInterface;
    public function setPort(int $port): RouteConditionHandlerInterface;
    public function setScheme(string $scheme): RouteConditionHandlerInterface;

    public function getMiddlewareStack(): iterable;
    public function lazyMiddleware(string $middleware): MiddlewareAwareInterface;
    public function lazyMiddlewares(array $middlewares): MiddlewareAwareInterface;
    public function lazyPrependMiddleware(string $middleware): MiddlewareAwareInterface;
    public function middleware(MiddlewareInterface $middleware): MiddlewareAwareInterface;
    public function middlewares(array $middlewares): MiddlewareAwareInterface;
    public function prependMiddleware(MiddlewareInterface $middleware): MiddlewareAwareInterface;
    public function shiftMiddleware(): MiddlewareInterface;

    public function getStrategy(): ?StrategyInterface;
    public function setStrategy(StrategyInterface $strategy): StrategyAwareInterface;

    public function middle(string $alias): RouteCollectorAwareTrait;
    public function strategy(string $alias): RouteCollectorAwareTrait;
}