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
use Illuminate\Support\Str;
use Rocketeer\Abstracts\AbstractTask;
use Rocketeer\Console\Commands\BaseTaskCommand;
use Rocketeer\Interfaces\IdentifierInterface;
use Rocketeer\Tasks;
use Rocketeer\Traits\HasLocator;

/**
 * Handles the registering and firing of tasks and their events
 *
 * @author Maxime Fabre <ehtnam6@gmail.com>
 */
class TasksHandler
{
    use HasLocator;

    /**
     * The registered events
     *
     * @type array
     */
    protected $registeredEvents = array();

    /**
     * The registered plugins
     *
     * @type array
     */
    protected $registeredPlugins = array();

    /**
     * Build a new TasksQueue Instance
     *
     * @param Container $app
     */
    public function __construct(Container $app)
    {
        $this->app = $app;
    }

    /**
     * Delegate methods to TasksQueue for now to
     * keep public API intact
     *
     * @param string $method
     * @param array  $parameters
     *
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        // Delegate calls to TasksQueue for facade purposes
        if (method_exists($this->queue, $method)) {
            return call_user_func_array(array($this->queue, $method), $parameters);
        }

        // Else we execute actions on the task
        $task = array_shift($parameters);
        $task = $this->builder->buildTask($task);
        call_user_func_array([$task, $method], $parameters);

        // And save it
        $this->app->instance('rocketeer.tasks.'.$task->getSlug(), $task);
    }

    ////////////////////////////////////////////////////////////////////
    ///////////////////////////// REGISTRATION /////////////////////////
    ////////////////////////////////////////////////////////////////////

    /**
     * Register a custom task with Rocketeer
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

        // Bind to framework too
        if ($framework = $this->getFramework()) {
            $command = $this->builder->buildCommand($task);
            $framework->registerConsoleCommand($command);
        }

        return $bound;
    }

    /**
     * Register a task with Rocketeer
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
     * Execute a task before another one
     *
     * @param string  $task
     * @param Closure $listeners
     * @param integer $priority
     */
    public function before($task, $listeners, $priority = 0)
    {
        $this->addTaskListeners($task, 'before', $listeners, $priority);
    }

    /**
     * Execute a task after another one
     *
     * @param string  $task
     * @param Closure $listeners
     * @param integer $priority
     */
    public function after($task, $listeners, $priority = 0)
    {
        $this->addTaskListeners($task, 'after', $listeners, $priority);
    }

    /**
     * Clear the previously registered events
     */
    public function clearRegisteredEvents()
    {
        foreach ($this->registeredEvents as $event) {
            $this->events->forget($event);
        }

        $this->registeredEvents = [];
    }

    /**
     * Register with the Dispatcher the events in the configuration
     */
    public function registerConfiguredEvents()
    {
        // Clean previously registered events
        $this->clearRegisteredEvents();

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
        unset($hooks['roles']);

        // Bind events
        foreach ($hooks as $event => $tasks) {
            foreach ($tasks as $task => $listeners) {
                $this->addTaskListeners($task, $event, $listeners, 0, true);
            }
        }

        // Bind core events
        $this->registerCoreEvents();

        // Assign roles
        $roles = (array) $this->rocketeer->getOption('hooks.roles');
        $this->roles->assignTasksRoles($roles);
    }

    /**
     * Bind the core events
     */
    public function registerCoreEvents()
    {
        $events = array(
            'commands.deploy.before' => 'Primer',
            'deploy.before-symlink'  => [['rocketeer.coordinator', 'beforeSymlink']],
        );

        foreach ($events as $event => $listeners) {
            $this->registeredEvents[] = 'rocketeer.'.$event;
            $priority                 = $event === 'deploy.before-symlink' ? -50 : 0;
            $this->listenTo($event, $listeners, $priority);
        }
    }

    /**
     * Register listeners for a particular event
     *
     * @param string         $event
     * @param array|callable $listeners
     * @param integer        $priority
     *
     * @return string
     */
    public function listenTo($event, $listeners, $priority = 0)
    {
        /** @type AbstractTask[] $listeners */
        $listeners = $this->builder->isCallable($listeners) ? [$listeners] : (array) $listeners;
        $listeners = $this->builder->buildTasks($listeners);
        $event     = Str::contains($event, ['commands.', 'tasks.']) ? $event : 'tasks.'.$event;

        // Register events
        foreach ($listeners as $listener) {
            $handle = $this->getEventHandle(null, $event);
            $listener->setEvent($handle);
            $this->events->listen($handle, [$listener, 'fire'], $priority);
        }

        return $event;
    }

    /**
     * Bind a listener to a task
     *
     * @param string|array   $task
     * @param string         $event
     * @param array|callable $listeners
     * @param integer        $priority
     * @param boolean        $register
     *
     * @throws \Rocketeer\Exceptions\TaskCompositionException
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

        // Cancel if no listeners
        if (!$listeners) {
            return;
        }

        // Prevent events on anonymous tasks
        $task = $this->builder->buildTask($task);
        if ($task->getSlug() === 'closure') {
            return;
        }

        // Get event name and register listeners
        $event = $this->getEventHandle($task, $event);
        $event = $this->listenTo($event, $listeners, $priority);

        // Store registered event
        if ($register) {
            $this->registeredEvents[] = $event;
        }

        return $event;
    }

    /**
     * Get all of a task's listeners
     *
     * @param string|AbstractTask $task
     * @param string              $event
     * @param boolean             $flatten
     *
     * @return array
     */
    public function getTasksListeners($task, $event, $flatten = false)
    {
        // Get events
        $task   = $this->builder->buildTaskFromClass($task);
        $handle = $this->getEventHandle($task, $event);
        $events = $this->events->getListeners($handle);

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
     * Register a Rocketeer plugin with Rocketeer
     *
     * @param string $plugin
     * @param array  $configuration
     */
    public function plugin($plugin, array $configuration = array())
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

        $this->registeredPlugins[$identifier] = array(
            'plugin'        => $plugin,
            'configuration' => $configuration,
        );

        // Register configuration
        $vendor = $plugin->getNamespace();
        $this->config->package('rocketeers/'.$vendor, $plugin->configurationFolder);
        if ($configuration) {
            $this->config->set($vendor.'::config', $configuration);
        }

        // Bind instances
        $this->app = $plugin->register($this->app);
        $plugin->onConsole($this->app['rocketeer.console']);
        $plugin->onBuilder($this->app['rocketeer.builder']);

        // Add hooks to TasksHandler
        $plugin->onQueue($this);
    }

    //////////////////////////////////////////////////////////////////////
    ////////////////////////////// HELPERS ///////////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * Get the handle of an event
     *
     * @param IdentifierInterface $entity
     * @param string|null         $event
     *
     * @return string
     */
    public function getEventHandle(IdentifierInterface $entity = null, $event = null)
    {
        // Concatenate identifier and event if it's not already done
        $event = $entity ? $entity->getIdentifier().'.'.$event : $event;
        $event = str_replace('rocketeer.', null, $event);

        return 'rocketeer.'.$event;
    }
}
