<?php

declare(strict_types=1);

namespace Pollen\Routing\Concerns;

use InvalidArgumentException;
use RuntimeException;

trait StrategyAwareTrait
{
    /**
     * Définition d'une stratégie selon un alias de qualification.
     *
     * @param string $alias
     *
     * @return static
     */
    public function strategy(string $alias): self
    {
        if (!$this->getContainer()) {
            throw new RuntimeException('Strategy aliased declaration require dependency injection container');
        }

        if (!$this->getContainer()->has("routing.strategy.{$alias}")) {
            throw new InvalidArgumentException(
                sprintf('Strategy alias (%s) is not being managed by the container', $alias)
            );
        }

        $this->setStrategy($this->getContainer()->get("routing.strategy.{$alias}"));

        return $this;
    }
}