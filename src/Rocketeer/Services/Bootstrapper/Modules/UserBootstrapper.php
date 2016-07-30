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
use Symfony\Component\ClassLoader\Psr4ClassLoader;

class UserBootstrapper extends AbstractBootstrapperModule
{
    /**
     * Bootstrap the user's code.
     */
    public function bootstrapUserFiles()
    {
        $this->bootstrapApp();
        $this->bootstrapStandaloneFiles();
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
     */
    protected function bootstrapApp()
    {
        $folder = $this->paths->getUserlandPath();
        if (!$this->files->has($folder)) {
            return;
        }

        $namespace = $this->getUserNamespace();

        // Load main namespace
        $classloader = new Psr4ClassLoader();
        $classloader->addPrefix($namespace.'\\', $this->files->getAdapter()->applyPathPrefix($folder));
        $classloader->register();

        // Load service provider
        $serviceProvider = $namespace.'\\'.$namespace.'ServiceProvider';
        if (class_exists($serviceProvider)) {
            $this->container->addServiceProvider($serviceProvider);
        }
    }

    /**
     * Bootstrap standalone files in the user's directory.
     */
    protected function bootstrapStandaloneFiles()
    {
        $folder = $this->paths->getRocketeerPath();
        $appFolderPath = trim($this->paths->getUserlandPath(), '/');
        $files = $this->files->listContents($folder, true);

        // Gather files to include in the correct order
        $queue = [];
        foreach ($files as $file) {
            $path = $file['path'];
            $isDir = $file['type'] === 'dir';
            $isPhp = !$isDir && isset($file['extension']) ? $file['extension'] === 'php' : false;

            if ($isDir || !$isPhp || strpos($path, $appFolderPath) !== false) {
                continue;
            }

            // Load tasks first
            if (strpos($path, 'tasks') !== false) {
                array_unshift($queue, $path);
            } else {
                $queue[] = $path;
            }
        }

        // Include files
        foreach ($queue as $file) {
            $this->files->includeFile($file);
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
