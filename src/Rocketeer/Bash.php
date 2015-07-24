<?php

/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Rocketeer;

use Rocketeer\Traits\BashModules\Binaries;
use Rocketeer\Traits\BashModules\Core;
use Rocketeer\Traits\BashModules\Filesystem;
use Rocketeer\Traits\BashModules\Flow;

/**
 * An helper to execute low-level commands on the remote server.
 *
 * @author Maxime Fabre <ehtnam6@gmail.com>
 */
class Bash
{
    use Core;
    use Binaries;
    use Filesystem;
    use Flow;

    /**
     * @param string $hook
     * @param array  $arguments
     *
     * @return string|array|null
     */
    protected function getHookedTasks($hook, array $arguments)
    {
        $tasks = $this->rocketeer->getOption('strategies.'.$hook);
        if (!is_callable($tasks)) {
            return;
        }

        // Cancel if no tasks to execute
        $tasks = (array) call_user_func_array($tasks, $arguments);
        if (empty($tasks)) {
            return;
        }

        // Run commands
        return $tasks;
    }

    //////////////////////////////////////////////////////////////////////
    ////////////////////////////// RUNNERS ///////////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * Get the implementation behind a strategy.
     *
     * @param string      $strategy
     * @param string|null $concrete
     *
     * @return \Rocketeer\Abstracts\Strategies\AbstractStrategy
     */
    public function getStrategy($strategy, $concrete = null)
    {
        $strategy = $this->builder->buildStrategy($strategy, $concrete);
        if (!$strategy || !$strategy->isExecutable()) {
            return;
        }

        return $this->explainer->displayBelow(function () use ($strategy) {
            return $strategy->displayStatus();
        });
    }

    /**
     * Execute another task by name.
     *
     * @param string $tasks
     *
     * @return string|false
     */
    public function executeTask($tasks)
    {
        $results = $this->explainer->displayBelow(function () use ($tasks) {
            return $this->builder->buildTask($tasks)->fire();
        });

        return $results;
    }
}
