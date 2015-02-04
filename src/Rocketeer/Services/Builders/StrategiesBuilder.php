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
     * @return \Rocketeer\Abstracts\Strategies\AbstractStrategy|false
     */
    public function buildStrategy($strategy, $concrete = null)
    {
        // If we passed a concrete implementation
        // build it, otherwise get the bound one
        $handle = strtolower($strategy);
        if ($concrete) {
            $concrete = $this->findQualifiedName($concrete, 'strategies', $strategy);

            if (!$concrete) {
                return false;
            }

            return new $concrete($this->app);
        }

        // Cancel if no matching strategy instance
        $handle = 'rocketeer.strategies.'.$handle;
        if (!$this->app->bound($handle)) {
            return;
        }

        return $this->app[$handle];
    }
}
