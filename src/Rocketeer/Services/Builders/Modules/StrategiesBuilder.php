<?php

/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace Rocketeer\Services\Builders\Modules;

/**
 * @author Maxime Fabre <ehtnam6@gmail.com>
 */
class StrategiesBuilder extends AbstractBuilderModule
{
    /**
     * Build a strategy.
     *
     * @param string      $strategy
     * @param string|null $concrete
     *
     * @return \Rocketeer\Strategies\AbstractStrategy|\Rocketeer\Interfaces\Strategies\FrameworkStrategyInterface|false
     */
    public function buildStrategy($strategy, $concrete = null)
    {
        // If we passed a concrete implementation
        // look for it specifically
        $handle = strtolower($strategy);
        if ($concrete) {
            $handle .= '.'.strtolower($concrete);
        }

        // If no found instance, create a new one
        $handle = 'rocketeer.strategies.'.$handle;
        if (!$this->container->has($handle)) {
            $concrete = $concrete ?: $this->config->getContextually('strategies.'.strtolower($strategy));
            $strategy = $this->buildStrategyFromName($strategy, $concrete);
            if (!$strategy) {
                return;
            }

            $this->container->add($handle, $strategy);
        }

        return $this->container->get($handle);
    }

    /**
     * Find a build a strategy by its class name.
     *
     * @param string $strategy
     * @param string $concrete
     *
     * @return object|false
     */
    protected function buildStrategyFromName($strategy, $concrete)
    {
        $concrete = $this->modulable->findQualifiedName($concrete, 'strategies', $strategy);

        if (!$concrete) {
            return false;
        }

        return new $concrete($this->container);
    }

    /**
     * @return string[]
     */
    public function getProvided()
    {
        return [
            'buildStrategy',
        ];
    }
}
