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

use Rocketeer\Strategies\AbstractPolyglotStrategy;

/**
 * Runs all of the above package managers if necessary.
 */
class PolyglotStrategy extends AbstractPolyglotStrategy implements DependenciesStrategyInterface
{
    /**
     * The type of the sub-strategies.
     *
     * @var string
     */
    protected $type = 'Dependencies';

    /**
     * @var string
     */
    protected $description = 'Runs all of the above package managers if necessary';

    /**
     * The various strategies to call.
     *
     * @var array
     */
    protected $strategies = ['Bundler', 'Composer', 'Npm', 'Bower'];

    /**
     * Install the dependencies.
     *
     * @return bool
     */
    public function install()
    {
        return $this->checkStrategiesMethod('install');
    }

    /**
     * Update the dependencies.
     *
     * @return bool
     */
    public function update()
    {
        return $this->checkStrategiesMethod('update');
    }
}
