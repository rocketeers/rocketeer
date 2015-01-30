<?php
/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Rocketeer\Services\Builders;

use Closure;
use Illuminate\Support\Str;
use Rocketeer\Abstracts\AbstractTask;
use Rocketeer\Exceptions\TaskCompositionException;
use Rocketeer\Tasks\Closure as ClosureTask;

/**
 * Handles creating tasks from strings, closures, AbstractTask children, etc.
 *
 * @author Maxime Fabre <ehtnam6@gmail.com>
 */
trait TasksBuilder
{
    /**
     * Build an array of tasks
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
     * Build a task from anything
     *
     * @param string|Closure|AbstractTask $task
     * @param string|null                 $name
     * @param string|null                 $description
     *
     * @throws \Rocketeer\Exceptions\TaskCompositionException
     * @return AbstractTask
     */
    public function buildTask($task, $name = null, $description = null)
    {
        // Compose the task from their various types
        $task = $this->composeTask($task);

        // If the built class is invalid, cancel
        if (!$task instanceof AbstractTask) {
            throw new TaskCompositionException('Class '.get_class($task).' is not a valid task');
        }

        // Set task properties
        $task->setName($name);
        $task->setDescription($description);

        return $task;
    }

    //////////////////////////////////////////////////////////////////////
    ////////////////////////////// COMPOSING /////////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * Compose a Task from its various types
     *
     * @param string|Closure|AbstractTask $task
     *
     * @return mixed|AbstractTask
     * @throws \Rocketeer\Exceptions\TaskCompositionException
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
        if ($task instanceof Closure) {
            return $this->buildTaskFromClosure($task);
        }

        // If we passed a task handle, return it
        if ($handle = $this->getTaskHandle($task)) {
            return $this->app[$handle];
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
     * Build a task from a string
     *
     * @param string|string[]|null $task
     *
     * @return AbstractTask
     */
    public function buildTaskFromString($task)
    {
        $closure = $this->wrapStringTasks($task);

        return $this->buildTaskFromClosure($closure, $task);
    }

    /**
     * Build a task from a Closure or a string command
     *
     * @param callable    $callback
     * @param string|null $stringTask
     *
     * @return AbstractTask
     */
    public function buildTaskFromClosure(callable $callback, $stringTask = null)
    {
        /** @type ClosureTask $task */
        $task = $this->buildTaskFromClass('Rocketeer\Tasks\Closure');
        $task->setClosure($callback);

        // If we had an original string used, store it on
        // the task for easier reflection
        if ($stringTask) {
            $task->setStringTask($stringTask);
        }

        return $task;
    }

    /**
     * Build a task from its name
     *
     * @param string|AbstractTask $task
     *
     * @throws TaskCompositionException
     * @return AbstractTask
     */
    public function buildTaskFromClass($task)
    {
        if (is_object($task) && $task instanceof AbstractTask) {
            return $task;
        }

        // Cancel if class doesn't exist
        if (!$class = $this->taskClassExists($task)) {
            throw new TaskCompositionException('Impossible to build task: '.$task);
        }

        return new $class($this->app);
    }

    /**
     * Build a task from a callable
     *
     * @param callable $callable
     *
     * @return ClosureTask
     */
    protected function buildTaskFromCallable($callable)
    {
        $task = new ClosureTask($this->app);
        $task->setClosure(function () use ($callable, $task) {
            $callable = is_array($callable) ? $callable : explode('::', $callable);

            return call_user_func_array([$this->app->make($callable[0]), $callable[1]], [$task]);
        });

        return $task;
    }

    //////////////////////////////////////////////////////////////////////
    ////////////////////////////// LOOKUPS ///////////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * Check if a class with the given task name exists
     *
     * @param string $task
     *
     * @return string|false
     */
    protected function taskClassExists($task)
    {
        return $this->findQualifiedName($task, 'tasks');
    }

    ////////////////////////////////////////////////////////////////////
    /////////////////////////////// HELPERS ////////////////////////////
    ////////////////////////////////////////////////////////////////////

    /**
     * Get the handle of a task from its name
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
        $handle = 'rocketeer.tasks.'.Str::snake($task, '-');
        $task   = $this->app->bound($handle) ? $handle : null;

        return $task;
    }

    /**
     * Check if a string is a command or a task
     *
     * @param string|Closure|AbstractTask $string
     *
     * @return boolean
     */
    protected function isStringCommand($string)
    {
        return is_string($string) && !$this->taskClassExists($string) && !$this->app->bound('rocketeer.tasks.'.$string);
    }

    /**
     * Check if a task is a callable
     *
     * @param array|string $task
     *
     * @return boolean
     */
    protected function isCallable($task)
    {
        // Check for container bindings
        if (is_array($task)) {
            return count($task) === 2 && $this->app->bound($task[0]) || is_callable($task);
        }

        return is_callable($task) && !$task instanceof Closure;
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
}
