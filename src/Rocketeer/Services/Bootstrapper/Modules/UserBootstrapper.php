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

use Illuminate\Support\Str;
use Rocketeer\Services\Builders\TaskCompositionException;

/**
 * Loads the user's .rocketeer folder and registers
 * his/her custom code.
 */
class UserBootstrapper extends AbstractBootstrapperModule
{
    /**
     * Bootstrap the user's code.
     */
    public function bootstrapUserFiles()
    {
        if (!$this->bootstrapApp()) {
            $this->bootstrapStandaloneFiles();
        }

        $this->bootstrapUserCode();
    }

    /**
     * @return string
     */
    public function getUserNamespace()
    {
        $name = $this->config->get('application_name');
        $name = Str::studly($name);

        return $name;
    }

    /**
     * Bootstrap a PSR4 folder in the user's directory.
     *
     * @return bool
     */
    protected function bootstrapApp()
    {
        $namespace = $this->getUserNamespace();

        // Load service provider
        $serviceProvider = $namespace.'\\'.$namespace.'ServiceProvider';
        $hasServiceProvider = class_exists($serviceProvider);
        if ($hasServiceProvider) {
            $this->container->addServiceProvider($serviceProvider);
        }

        return $hasServiceProvider;
    }

    /**
     * Bootstrap the user's standalone files
     */
    protected function bootstrapStandaloneFiles()
    {
        $files = $this->files->listFiles($this->paths->getUserlandPath(), true);

        // Build queue, placing tasks first, events after
        $queue = [];
        foreach ($files as $file) {
            $path = $this->files->getAdapter()->applyPathPrefix($file['path']);
            if (strpos($path, 'tasks') !== false) {
                array_unshift($queue, $path);
            } else {
                $queue[] = $path;
            }
        }

        // Include files
        foreach ($queue as $path) {
            include $path;
        }
    }

    /**
     * Register the user's tasks, events and roles.
     */
    public function bootstrapUserCode()
    {
        // Clean previously registered events
        $this->tasks->clearRegisteredEvents();

        // Re-register events
        foreach ($this->container->getPlugins() as $plugin) {
            if (strpos($plugin, $this->getUserNamespace().'ServiceProvider') === false) {
                $this->container->addServiceProvider($plugin);
            }
        }

        // Get the registered events
        $hooks = (array) $this->config->getContextually('hooks');
        $tasks = isset($hooks['tasks']) ? (array) $hooks['tasks'] : [];
        $roles = isset($hooks['roles']) ? (array) $hooks['roles'] : [];
        $events = isset($hooks['events']) ? (array) $hooks['events'] : [];

        // Bind tasks and commands
        foreach ($tasks as $name => $task) {
            try {
                $this->tasks->task($name, $task);
            } catch (TaskCompositionException $exception) {
                $this->tasks->command($name, $task);
            }
        }

        // Bind events
        foreach ($events as $event => $tasks) {
            foreach ($tasks as $task => $listeners) {
                $this->tasks->addTaskListeners($task, $event, $listeners, 0, true);
            }
        }

        // Assign roles
        $this->roles->assignTasksRoles($roles);
    }

    /**
     * @return string[]
     */
    public function getProvided()
    {
        return [
            'bootstrapUserFiles',
            'bootstrapUserCode',
            'getUserNamespace',
        ];
    }
}
