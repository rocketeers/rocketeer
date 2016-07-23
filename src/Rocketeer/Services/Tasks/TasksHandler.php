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

namespace Rocketeer\Services\Tasks;

use Closure;
use Illuminate\Support\Str;
use Rocketeer\Console\Commands\BaseTaskCommand;
use Rocketeer\Container;
use Rocketeer\Interfaces\IdentifierInterface;
use Rocketeer\Tasks\AbstractTask;
use Rocketeer\Tasks\Closure as ClosureTask;
use Rocketeer\Traits\ContainerAwareTrait;

/**
 * Handles the registering and firing of tasks and their events.
 *
 * @author Maxime Fabre <ehtnam6@gmail.com>
 */
class TasksHandler
{
    use ContainerAwareTrait;

    /**
     * The registered events.
     *
     * @var array
     */
    protected $registeredEvents = [];

    /**
     * Build a new TasksQueue Instance.
     *
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Delegate methods to TasksQueue for now to
     * keep public API intact.
     *
     * @param string $method
     * @param array  $arguments
     *
     * @return mixed
     */
    public function __call($method, $arguments)
    {
        // Delegate calls to TasksQueue for facade purposes
        if (method_exists($this->queue, $method)) {
            return $this->queue->$method(...$arguments);
        }

        // Else we execute actions on the task
        $this->delegateAndRebind($method, $arguments, 'buildTask');
    }

    /**
     * Configure a strategy.
     *
     * @param array ...$arguments
     */
    public function configureStrategy(...$arguments)
    {
        $this->delegateAndRebind('configure', $arguments, 'buildStrategy');
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
        $this->container->add($slug, $task);
        $bound = $this->console->add(new BaseTaskCommand($this->container->get($slug)));

        return $bound;
    }

    /**
     * Register a task with Rocketeer.
     *
     * @param string                                            $name
     * @param string|Closure|\Rocketeer\Tasks\AbstractTask|null $task
     * @param string|null                                       $description
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
     * Clear the previously registered events.
     */
    public function clearRegisteredEvents()
    {
        foreach ($this->registeredEvents as $event) {
            $this->events->removeAllListeners($event);
        }

        $this->registeredEvents = [];
    }

    /**
     * Register with the Dispatcher the events in the configuration.
     */
    public function registerConfiguredEvents()
    {
        // Clean previously registered events
        $this->clearRegisteredEvents();

        // Re-register events
        foreach ($this->container->getPlugins() as $plugin) {
            $this->container->addServiceProvider($plugin);
        }

        // Get the registered events
        $hooks = (array) $this->config->getContextually('hooks');
        $tasks = isset($hooks['tasks']) ? (array) $hooks['tasks'] : [];
        $roles = isset($hooks['roles']) ? (array) $hooks['roles'] : [];
        $events = isset($hooks['events']) ? (array) $hooks['events'] : [];

        // Bind tasks
        foreach ($tasks as $name => $task) {
            $this->task($name, $task);
        }

        // Bind events
        foreach ($events as $event => $tasks) {
            foreach ($tasks as $task => $listeners) {
                $this->addTaskListeners($task, $event, $listeners, 0, true);
            }
        }

        // Assign roles
        $this->roles->assignTasksRoles($roles);
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
        /** @var \Rocketeer\Tasks\AbstractTask[] $listeners */
        $listeners = $this->builder->isCallable($listeners) ? [$listeners] : (array) $listeners;
        $listeners = $this->builder->buildTasks($listeners);
        $event = Str::contains($event, ['commands.', 'strategies.', 'tasks.']) ? $event : 'tasks.'.$event;

        // Register events
        foreach ($listeners as $key => $listener) {
            $handle = $this->getEventHandle(null, $event);
            $this->events->addListener($handle, $listener, $priority ?: -$key);
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
     * @throws \Rocketeer\Services\Builders\TaskCompositionException
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
        $task = $this->builder->buildTaskFromClass($task);
        $handle = $this->getEventHandle($task, $event);
        $events = $this->events->getListeners($handle);

        // Flatten the queue if requested
        foreach ($events as $key => $listener) {
            if ($flatten && $listener instanceof ClosureTask && $stringTask = $listener->getStringTask()) {
                $events[$key] = $stringTask;
            } elseif ($flatten && $listener instanceof AbstractTask) {
                $events[$key] = $listener->getSlug();
            }
        }

        return $events;
    }

    //////////////////////////////////////////////////////////////////////
    ////////////////////////////// HELPERS ///////////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * Call a method on an object, and rebind it into the container.
     *
     * @param string $method
     * @param array  $parameters
     * @param string $builder
     *
     * @throws \Rocketeer\Services\Builders\TaskCompositionException
     */
    protected function delegateAndRebind($method, array $parameters, $builder)
    {
        $object = (array) array_shift($parameters);
        $object = $this->builder->$builder(...$object);
        $object->$method(...$parameters);

        $this->container->add('rocketeer.'.$object->getIdentifier(), $object);
    }

    /**
     * Get the handle of an event.
     *
     * @param IdentifierInterface|null $entity
     * @param string|null              $event
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
