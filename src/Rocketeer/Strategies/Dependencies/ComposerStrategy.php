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

use Rocketeer\Abstracts\Strategies\AbstractDependenciesStrategy;
use Rocketeer\Interfaces\Strategies\DependenciesStrategyInterface;

class ComposerStrategy extends AbstractDependenciesStrategy implements DependenciesStrategyInterface
{
    /**
     * @type string
     */
    protected $description = 'Installs dependencies with Composer';

    /**
     * The name of the binary.
     *
     * @type string
     */
    protected $binary = 'composer';

    /**
     * Install the dependencies.
     *
     * @return bool
     */
    public function install()
    {
        return $this->executeHook('install');
    }

    /**
     * Update the dependencies.
     *
     * @return bool
     */
    public function update()
    {
        return $this->executeHook('update');
    }

    /**
     * @param string $hook
     *
     * @return bool
     */
    protected function executeHook($hook)
    {
        $tasks = $this->getHookedTasks('composer.'.$hook, [$this->manager, $this]);
        if (!$tasks) {
            return true;
        }

        $this->runForCurrentRelease($tasks);

        return $this->checkStatus('Composer could not install dependencies');
    }
}
