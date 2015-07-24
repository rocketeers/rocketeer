<?php

/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Rocketeer\Strategies\Dependencies;

use Rocketeer\Abstracts\Strategies\AbstractPolyglotStrategy;
use Rocketeer\Interfaces\Strategies\DependenciesStrategyInterface;

class PolyglotStrategy extends AbstractPolyglotStrategy implements DependenciesStrategyInterface
{
    /**
     * @type string
     */
    protected $description = 'Runs all of the above package managers if necessary';

    /**
     * The various strategies to call.
     *
     * @type array
     */
    protected $strategies = ['Bundler', 'Composer', 'Npm', 'Bower'];

    /**
     * Install the dependencies.
     *
     * @return boolean[]
     */
    public function install()
    {
        return $this->executeStrategiesMethod('install');
    }

    /**
     * Update the dependencies.
     *
     * @return boolean[]
     */
    public function update()
    {
        return $this->executeStrategiesMethod('update');
    }
}
