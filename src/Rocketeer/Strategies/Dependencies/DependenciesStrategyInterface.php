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

namespace Rocketeer\Strategies\Dependencies;

/**
 * A strategy for how to install/update an application's dependencies.
 */
interface DependenciesStrategyInterface
{
    /**
     * Install the dependencies.
     *
     * @return bool
     */
    public function install();

    /**
     * Update the dependencies.
     *
     * @return bool
     */
    public function update();
}
