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

namespace Rocketeer\Services\Builders\Modules;

use Closure;
use Illuminate\Support\Str;
use League\Container\ContainerAwareInterface;
use Rocketeer\Services\Builders\TaskCompositionException;
use Rocketeer\Tasks\AbstractTask;
use Rocketeer\Tasks\Closure as ClosureTask;
use SuperClosure\SerializableClosure;

/**
 * Handles creating tasks from strings, closures, AbstractTask children, etc.
 */
class TasksBuilder extends AbstractBuilderModule
{
    /**
     * Build an array of tasks.
     *
     * @param array $tasks
     *
     * @return array
     */
    public function buildTasks(array $tasks)
    {
        return array_map([$this, 'buildTask'], $tasks);
    }

    /**
     * Build a task from anything.
     *
     * @param string|Closure|\Rocketeer\Tasks\AbstractTask $task
     * @param string|null                                  $name
     * @param string|null                                  $description
     *
     * @throws \Rocketeer\Services\Builders\TaskCompositionException
     *
     * @return AbstractTask
     */
    public function buildTask($task, $name = null, $description = null)
    {
        // Compose the task from their various types
        $task = $this->composeTask($task);

        // If the built class is invalid, cancel
        if (!$task instanceof AbstractTask) {
            throw new TaskCompositionException($task);
        }

        // Set task properties
        $task->setName($name);
        $task->setDescription($description);
        $task = $this->modulable->registerBashModulesOn($task);

        // Bind instance for later user
        if (!$task instanceof ClosureTask) {
            $this->container->add('rocketeer.'.$task->getIdentifier(), $task);
        }

        return $task;
    }

    //////////////////////////////////////////////////////////////////////
    ////////////////////////////// COMPOSING /////////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * Compose a Task from its various types.
     *
     * @param string|Closure|AbstractTask $task
     *
     * @throws \Rocketeer\Services\Builders\TaskCompositionException
     *
     * @return mixed|\Rocketeer\Tasks\AbstractTask
     */
    protected function composeTask($task)
    {
        // If already built, return it
        if ($task instanceof AbstractTask) {
            return $task;
        }

        // If we passed a callable, build a Closure Task
        if ($this->isCallable($task)) {
            return $this->buildTaskFromCallable($task);
        }

        // If we provided a Closure, build a Closure Task
        if ($task instanceof Closure || $task instanceof SerializableClosure) {
            return $this->buildTaskFromClosure($task);
        }

        // If we passed a task handle, return it
        if ($handle = $this->getTaskHandle($task)) {
            return $this->container->get($handle);
        }

        // If we passed a command, build a Closure Task
        if (is_array($task) || $this->isStringCommand($task) || $task === null) {
            return $this->buildTaskFromString($task);
        }

        // Else it's a class name, get the appropriated task
        if (!$task instanceof AbstractTask) {
            return $this->buildTaskFromClass($task);
        }
    }

    /**
     * Build a task from a string.
     *
     * @param string|string[] $task
     *
     * @return AbstractTask
     */
    public function buildTaskFromString($task)
    {
        $closure = $this->wrapStringTasks($task);

        return $this->buildTaskFromClosure($closure, $task);
    }

    /**
     * Build a task from a Closure or a string command.
     *
     * @param SerializableClosure|Closure $callback
     * @param string|null                 $stringTask
     *
     * @return \Rocketeer\Tasks\AbstractTask
     */
    public function buildTaskFromClosure($callback, $stringTask = null)
    {
        /** @var ClosureTask $task */
        $task = $this->buildTaskFromClass(ClosureTask::class);
        $task->setClosure($callback);

        // If we had an original string used, store it on
        // the task for easier reflection
        if ($stringTask) {
            $task->setStringTask($stringTask);
        }

        return $task;
    }

    /**
     * Build a task from its name.
     *
     * @param string|\Rocketeer\Tasks\AbstractTask $task
     *
     * @throws TaskCompositionException
     *
     * @return AbstractTask
     */
    public function buildTaskFromClass($task)
    {
        if (is_object($task) && $task instanceof AbstractTask) {
            return $task;
        }

        // Cancel if class doesn't exist
        if (!$class = $this->taskClassExists($task)) {
            throw new TaskCompositionException($task);
        }

        // Build class
        $class = new $class();
        if ($class instanceof ContainerAwareInterface) {
            $class->setContainer($this->container);
        }

        return $class;
    }

    /**
     * Build a task from a callable.
     *
     * @param callable $callable
     *
     * @return ClosureTask
     */
    protected function buildTaskFromCallable($callable)
    {
        $task = new ClosureTask();
        $task->setContainer($this->container);
        $task->setClosure(function () use ($callable, $task) {
            list($class, $method) = is_array($callable) ? $callable : explode('::', $callable);

            return $this->getContainer()->get($class)->$method($task);
        });

        return $task;
    }

    //////////////////////////////////////////////////////////////////////
    ////////////////////////////// LOOKUPS ///////////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * Check if a class with the given task name exists.
     *
     * @param string $task
     *
     * @return string|false
     */
    protected function taskClassExists($task)
    {
        return $this->modulable->findQualifiedName($task, 'tasks');
    }

    ////////////////////////////////////////////////////////////////////
    /////////////////////////////// HELPERS ////////////////////////////
    ////////////////////////////////////////////////////////////////////

    /**
     * Get the handle of a task from its name.
     *
     * @param string|AbstractTask $task
     *
     * @return string|null
     */
    protected function getTaskHandle($task)
    {
        // Check the handle if possible
        if (!is_string($task)) {
            return;
        }

        // Compute the handle and check it's bound
        $handle = 'rocketeer.tasks.'.Str::snake(class_basename($task), '-');
        $task = $this->container->has($handle) ? $handle : null;

        return $task;
    }

    /**
     * Check if a string is a command or a task.
     *
     * @param string|Closure|\Rocketeer\Tasks\AbstractTask $string
     *
     * @return bool
     */
    protected function isStringCommand($string)
    {
        return is_string($string) && !$this->taskClassExists($string) && !$this->container->has('rocketeer.tasks.'.$string);
    }

    /**
     * Check if a task is a callable.
     *
     * @param array|string|Closure $task
     *
     * @return bool
     */
    public function isCallable($task)
    {
        // Check for container bindings
        if (is_array($task)) {
            return count($task) === 2 && ($this->container->has($task[0]) || is_callable($task));
        }

        return is_callable($task) && !$task instanceof Closure && !$task instanceof SerializableClosure;
    }

    /**
     * @param string|array $stringTask
     *
     * @return Closure
     */
    public function wrapStringTasks($stringTask)
    {
        return function (AbstractTask $task) use ($stringTask) {
            return $task->runForCurrentRelease($stringTask);
        };
    }

    /**
     * @return string[]
     */
    public function getProvided()
    {
        return [
            'buildTask',
            'buildTaskFromClass',
            'buildTaskFromClosure',
            'buildTaskFromString',
            'buildTasks',
            'isCallable',
            'wrapStringTasks',
        ];
    }
}
