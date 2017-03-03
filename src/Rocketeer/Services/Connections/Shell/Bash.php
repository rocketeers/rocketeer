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

namespace Rocketeer\Services\Connections\Shell;

use League\Container\ContainerAwareInterface;
use Rocketeer\Services\Modules\ModulableInterface;
use Rocketeer\Services\Modules\ModulableTrait;
use Rocketeer\Traits\ContainerAwareTrait;
use Rocketeer\Traits\Properties\HasHistoryTrait;

/**
 * An helper to execute low-level commands on the remote server.
 *
 * @mixin Modules\Binaries
 * @mixin Modules\Core
 * @mixin Modules\Filesystem
 * @mixin Modules\Statuses
 * @mixin Modules\Flow
 *
 * @method bool displayStatusMessage($error, $output = null, $success = null)
 * @method string copyFromPreviousRelease($folder)
 * @method string fileExists($file)
 * @method string listContents($directory)
 * @method string run($commands, $silent = false, $array = false)
 * @method string runForApplication($tasks)
 * @method string share($file)
 * @method string which($binary, $fallback = null, $prompt = true)
 */
class Bash implements ModulableInterface, ContainerAwareInterface
{
    use ContainerAwareTrait;
    use HasHistoryTrait;
    use ModulableTrait;

    /**
     * Get which Connection to call commands with.
     *
     * @return \Rocketeer\Services\Connections\Connections\ConnectionInterface
     */
    public function getConnection()
    {
        if ($this->rocketeer->isLocal()) {
            return $this->connections->getConnection('local');
        }

        return $this->connections->getCurrentConnection();
    }

    /**
     * @param string   $connection
     * @param callable $callback
     *
     * @return bool
     */
    public function on($connection, callable $callback)
    {
        $current = $this->connections->getCurrentConnectionKey();

        $this->connections->setCurrentConnection($connection);
        $results = $callback($this);
        $this->connections->setCurrentConnection($current);

        return $results;
    }

    //////////////////////////////////////////////////////////////////////
    ////////////////////////////// RUNNERS ///////////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * Get the implementation behind a strategy.
     *
     * @param string      $strategy
     * @param string|null $concrete
     * @param array       $options
     *
     * @return \Rocketeer\Strategies\AbstractStrategy
     */
    public function getStrategy($strategy, $concrete = null, $options = [])
    {
        // Try to build the strategy
        $strategy = $this->builder->buildStrategy($strategy, $concrete);
        if (!$strategy || !$strategy->isExecutable()) {
            return;
        }

        // Configure strategy
        if ($options || $this->options) {
            $options = array_replace((array) $options, $strategy->getOptions(), $this->options);
            $strategy = $strategy->setOptions($options);
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
        return $this->explainer->displayBelow(function () use ($tasks) {
            return $this->builder->buildTask($tasks)->fire();
        });
    }

    /**
     * @param string $strategy
     * @param string $method
     *
     * @return mixed
     */
    public function executeStrategyMethod($strategy, $method)
    {
        $strategy = $this->getStrategy($strategy);
        if (!$strategy) {
            return true;
        }

        return $this->explainer->displayBelow(function () use ($strategy, $method) {
            return $strategy->$method();
        });
    }

    /**
     * @param string $hook
     * @param array  $arguments
     *
     * @return string|array|null
     */
    protected function getHookedTasks($hook, array $arguments)
    {
        $tasks = $this->config->getContextually('strategies.'.$hook);
        if (!is_callable($tasks)) {
            return;
        }

        // Cancel if no tasks to execute
        $tasks = (array) $tasks(...$arguments);
        if (empty($tasks)) {
            return;
        }

        // Run commands
        return $tasks;
    }
}
