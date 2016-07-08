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

namespace Rocketeer\Services\Ignition;

use KevinGH\Amend\Command;
use Rocketeer\Tasks\AbstractTask;
use Rocketeer\Traits\HasLocator;

class Tasks
{
    use HasLocator;

    /**
     * Get an array of all already defined tasks.
     *
     * @return array
     */
    public function getPredefinedTasks()
    {
        $tasks = [
            'check' => 'Check',
            'cleanup' => 'Cleanup',
            'current' => 'CurrentRelease',
            'deploy' => 'Deploy',
            'flush' => 'Flush',
            'ignite' => 'Ignite',
            'rollback' => 'Rollback',
            'setup' => 'Setup',
            'strategies' => 'Strategies',
            'teardown' => 'Teardown',
            'test' => 'Test',
            'update' => 'Update',
            'debug-tinker' => 'Development\Tinker',
            'debug-config' => 'Development\Configuration',
            'self-update' => 'Development\SelfUpdate',
            'plugin-publish' => 'Plugins\Publish',
            'plugin-list' => 'Plugins\List',
            'plugin-install' => 'Plugins\Install',
            'plugin-update' => 'Plugins\Update',
        ];

        // Add user commands
        $userTasks = (array) $this->config->get('hooks.custom');
        $userTasks = array_filter($userTasks);
        $tasks = array_merge($tasks, $userTasks);

        return $tasks;
    }

    /**
     * Register an array of tasks and their commands.
     *
     * @param array $tasks
     *
     * @return array The registered commands
     */
    public function registerTasksAndCommands(array $tasks)
    {
        $commands = [];

        foreach ($tasks as $slug => $task) {
            // Build the related command
            $command = $this->builder->buildCommand($task, $slug);
            $task = $command->getTask();

            // Bind task to container
            $slug = $this->getTaskHandle($slug, $task);
            $this->container->add('rocketeer.tasks.'.$slug, $task);

            // Remember handle of the command
            $commands[] = $command;
            $this->container->share('commands.'.$slug, function () use ($command) {
                return $command;
            });
        }

        // Add self update command
        $selfUpdate = new Command('self-update');
        $selfUpdate->setManifestUri('http://rocketeer.autopergamene.eu/versions/manifest.json');
        $commands[] = $selfUpdate;

        return $commands;
    }

    /**
     * Get the handle matching a task.
     *
     * @param string            $slug
     * @param AbstractTask|null $task
     *
     * @return string|null
     */
    public function getTaskHandle($slug, AbstractTask $task = null)
    {
        $slug = ($slug || !$task) ? $slug : $task->getSlug();
        if ($slug === 'closure') {
            return;
        }

        return $slug;
    }
}
