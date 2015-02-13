<?php
/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Rocketeer\Services\Builders;

trait StrategiesBuilder
{
    /**
     * Build a strategy
     *
     * @param string      $strategy
     * @param string|null $concrete
     *
     * @return \Rocketeer\Abstracts\Strategies\AbstractStrategy|\Rocketeer\Interfaces\Strategies\FrameworkStrategyInterface|false
     */
    public function buildStrategy($strategy, $concrete = null)
    {
        // If we passed a concrete implementation
        // look for it specifically
        $handle = strtolower($strategy);
        if ($concrete) {
            $handle .= '.'.strtolower($concrete);
        }

        // Cancel if no matching strategy instance
        $handle = 'rocketeer.strategies.'.$handle;
        if (!$this->app->bound($handle)) {
            return $concrete ? $this->buildStrategyFromName($strategy, $concrete) : null;
        }

        return $this->app[$handle];
    }

    /**
     * Find a build a strategy by its class name
     *
     * @param string $strategy
     * @param string $concrete
     *
     * @return boolean
     */
    protected function buildStrategyFromName($strategy, $concrete)
    {
        $concrete = $this->findQualifiedName($concrete, 'strategies', $strategy);

        if (!$concrete) {
            return false;
        }

        return new $concrete($this->app);
    }
}
