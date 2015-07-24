<?php

/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Rocketeer\Services;

use Closure;
use Illuminate\Container\Container;
use Rocketeer\Abstracts\AbstractTask;
use Rocketeer\Console\Commands\BaseTaskCommand;
use Rocketeer\Tasks;
use Rocketeer\Traits\HasLocator;

/**
 * Handles the registering and firing of tasks and their events.
 *
 * @author Maxime Fabre <ehtnam6@gmail.com>
 */
class TasksHandler
{
    use HasLocator;

    /**
     * The registered events.
     *
     * @type array
     */
    protected $registeredEvents = [];

    /**
     * The registered plugins.
     *
     * @type array
     */
    protected $registeredPlugins = [];

    /**
     * Build a new TasksQueue Instance.
     *
     * @param Container $app
     */
    public function __construct(Container $app)
    {
        $this->app = $app;
    }

    /**
     * Delegate methods to TasksQueue for now to
     * keep public API intact.
     *
     * @param string $method
     * @param array  $parameters
     *
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return call_user_func_array([$this->queue, $method], $parameters);
    }

    ////////////////////////////////////////////////////////////////////
    ///////////////////////////// REGISTRATION /////////////////////////
    ////////////////////////////////////////////////////////////////////

    /**
     * Register a custom task with Rocketeer.
     *
     * @param string|Closure|AbstractTask $task
     * @param string|null                 $name
     * @param string|null                 $description
     *
     * @return BaseTaskCommand
     */
    public function add($task, $name = null, $description = null)
    {
        // Build task if necessary
        $task = $this->builder->buildTask($task, $name, $description);
        $slug = 'rocketeer.tasks.'.$task->getSlug();

        // Add the task to Rocketeer
        $this->app->instance($slug, $task);
        $bound = $this->console->add(new BaseTaskCommand($this->app[$slug]));

        // Bind to Artisan too
        if ($this->app->bound('artisan') && $this->app->resolved('artisan')) {
            $command = $this->builder->buildCommand($task);
            $this->app['artisan']->add($command);
        }

        return $bound;
    }

    /**
     * Register a task with Rocketeer.
     *
     * @param string                           $name
     * @param string|Closure|AbstractTask|null $task
     * @param string|null                      $description
     *
     * @return BaseTaskCommand
     */
    public function task($name, $task = null, $description = null)
    {
        return $this->add($task, $name, $description)->getTask();
    }

    ////////////////////////////////////////////////////////////////////
    /////////////////////////////// EVENTS /////////////////////////////
    ////////////////////////////////////////////////////////////////////

    /**
     * Execute a task before another one.
     *
     * @param string  $task
     * @param Closure $listeners
     * @param int     $priority
     */
    public function before($task, $listeners, $priority = 0)
    {
        $this->addTaskListeners($task, 'before', $listeners, $priority);
    }

    /**
     * Execute a task after another one.
     *
     * @param string  $task
     * @param Closure $listeners
     * @param int     $priority
     */
    public function after($task, $listeners, $priority = 0)
    {
        $this->addTaskListeners($task, 'after', $listeners, $priority);
    }

    /**
     * Register with the Dispatcher the events in the configuration.
     */
    public function registerConfiguredEvents()
    {
        // Clean previously registered events
        foreach ($this->registeredEvents as $event) {
            $this->events->forget('rocketeer.'.$event);
        }

        // Clean previously registered plugins
        $plugins                 = $this->registeredPlugins;
        $this->registeredPlugins = [];

        // Register plugins again
        foreach ($plugins as $plugin) {
            $this->plugin($plugin['plugin'], $plugin['configuration']);
        }

        // Get the registered events
        $hooks = (array) $this->rocketeer->getOption('hooks');
        unset($hooks['custom']);

        // Bind events
        foreach ($hooks as $event => $tasks) {
            foreach ($tasks as $task => $listeners) {
                $this->addTaskListeners($task, $event, $listeners, 0, true);
            }
        }
    }

    /**
     * Register listeners for a particular event.
     *
     * @param string         $event
     * @param array|callable $listeners
     * @param int            $priority
     *
     * @return string
     */
    public function listenTo($event, $listeners, $priority = 0)
    {
        /** @type AbstractTask[] $listeners */
        $listeners = $this->builder->buildTasks((array) $listeners);

        // Register events
        foreach ($listeners as $listener) {
            $listener->setEvent($event);
            $this->events->listen('rocketeer.'.$event, [$listener, 'fire'], $priority);
        }

        return $event;
    }

    /**
     * Bind a listener to a task.
     *
     * @param string|array   $task
     * @param string         $event
     * @param array|callable $listeners
     * @param int            $priority
     * @param bool           $register
     *
     * @throws \Rocketeer\Exceptions\TaskCompositionException
     *
     * @return string|null
     */
    public function addTaskListeners($task, $event, $listeners, $priority = 0, $register = false)
    {
        // Recursive call
        if (is_array($task)) {
            foreach ($task as $t) {
                $this->addTaskListeners($t, $event, $listeners, $priority, $register);
            }

            return;
        }

        // Prevent events on anonymous tasks
        $slug = $this->builder->buildTask($task)->getSlug();
        if ($slug === 'closure') {
            return;
        }

        // Get event name and register listeners
        $event = $slug.'.'.$event;
        $event = $this->listenTo($event, $listeners, $priority);

        // Store registered event
        if ($register) {
            $this->registeredEvents[] = $event;
        }

        return $event;
    }

    /**
     * Get all of a task's listeners.
     *
     * @param string|AbstractTask $task
     * @param string              $event
     * @param bool                $flatten
     *
     * @return array
     */
    public function getTasksListeners($task, $event, $flatten = false)
    {
        // Get events
        $task   = $this->builder->buildTaskFromClass($task)->getSlug();
        $events = $this->events->getListeners('rocketeer.'.$task.'.'.$event);

        // Flatten the queue if requested
        foreach ($events as $key => $event) {
            $task = $event[0];
            if ($flatten && $task instanceof Tasks\Closure && $stringTask = $task->getStringTask()) {
                $events[$key] = $stringTask;
            } elseif ($flatten && $task instanceof AbstractTask) {
                $events[$key] = $task->getSlug();
            }
        }

        return $events;
    }

    ////////////////////////////////////////////////////////////////////
    /////////////////////////////// PLUGINS ////////////////////////////
    ////////////////////////////////////////////////////////////////////

    /**
     * @return array
     */
    public function getRegisteredPlugins()
    {
        return $this->registeredPlugins;
    }

    /**
     * Register a Rocketeer plugin with Rocketeer.
     *
     * @param string $plugin
     * @param array  $configuration
     */
    public function plugin($plugin, array $configuration = [])
    {
        // Build plugin
        if (is_string($plugin)) {
            $plugin = $this->app->make($plugin, [$this->app]);
        }

        // Store registration of plugin
        $identifier = get_class($plugin);
        if (isset($this->registeredPlugins[$identifier])) {
            return;
        }

        $this->registeredPlugins[$identifier] = [
            'plugin'        => $plugin,
            'configuration' => $configuration,
        ];

        // Register configuration
        $vendor = $plugin->getNamespace();
        $this->config->package('rocketeers/'.$vendor, $plugin->configurationFolder);
        if ($configuration) {
            $this->config->set($vendor.'::config', $configuration);
        }

        // Bind instances
        $this->app = $plugin->register($this->app);

        // Add hooks to TasksHandler
        $plugin->onQueue($this);
    }
}
