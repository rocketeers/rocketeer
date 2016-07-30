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

namespace Rocketeer\Services\Bootstrapper\Modules;

use Rocketeer\Tasks\Check;
use Rocketeer\Tasks\Cleanup;
use Rocketeer\Tasks\CurrentRelease;
use Rocketeer\Tasks\Dependencies;
use Rocketeer\Tasks\Deploy;
use Rocketeer\Tasks\Ignite;
use Rocketeer\Tasks\Migrate;
use Rocketeer\Tasks\Plugins\Installer;
use Rocketeer\Tasks\Plugins\Updater;
use Rocketeer\Tasks\Rollback;
use Rocketeer\Tasks\Setup;
use Rocketeer\Tasks\Subtasks\CreateRelease;
use Rocketeer\Tasks\Subtasks\FridayDeploy;
use Rocketeer\Tasks\Subtasks\Notify;
use Rocketeer\Tasks\Subtasks\Primer;
use Rocketeer\Tasks\Subtasks\SwapSymlink;
use Rocketeer\Tasks\Teardown;
use Rocketeer\Tasks\Test;
use Rocketeer\Tasks\Update;

/**
 * Register the core tasks with Rocketeer and bind
 * them and their CLI commands to the container.
 */
class TasksBootstrapper extends AbstractBootstrapperModule
{
    /**
     * Bootstrap tasks registration.
     */
    public function bootstrapTasks()
    {
        $tasks = [
            Check::class,
            Cleanup::class,
            CreateRelease::class,
            CurrentRelease::class,
            Dependencies::class,
            Deploy::class,
            FridayDeploy::class,
            Ignite::class,
            Installer::class,
            Migrate::class,
            Notify::class,
            Primer::class,
            Rollback::class,
            Setup::class,
            SwapSymlink::class,
            Teardown::class,
            Test::class,
            Update::class,
            Updater::class,
        ];

        foreach ($tasks as $task) {
            $task = new $task();
            $task->setContainer($this->container);
            $this->builder->buildTask($task);
        }
    }

    /**
     * @return string[]
     */
    public function getProvided()
    {
        return [
            'bootstrapTasks',
        ];
    }
}
