<?php

/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Rocketeer\Services\Tasks;

use Closure;
use Illuminate\Support\Str;
use Rocketeer\Abstracts\AbstractTask;
use Rocketeer\Binaries\AnonymousBinary;
use Rocketeer\Exceptions\TaskCompositionException;
use Rocketeer\Traits\HasLocator;

/**
 * Handles creating tasks from strings, closures, AbstractTask children, etc.
 *
 * @author Maxime Fabre <ehtnam6@gmail.com>
 */
class TasksBuilder
{
    use HasLocator;

    /**
     * Build a binary.
     *
     * @param string $binary
     *
     * @return \Rocketeer\Abstracts\AbstractBinary|\Rocketeer\Abstracts\AbstractPackageManager
     */
    public function buildBinary($binary)
    {
        $class = $this->findQualifiedName($binary, [
            'Rocketeer\Binaries\PackageManagers\%s',
            'Rocketeer\Binaries\%s',
        ]);

        // If there is a class by that name
        if ($class) {
            return new $class($this->app);
        }

        // Else wrap the command in an AnonymousBinary
        $anonymous = new AnonymousBinary($this->app);
        $anonymous->setBinary($binary);

        return $anonymous;
    }

    //////////////////////////////////////////////////////////////////////
    ////////////////////////////// COMMANDS //////////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * Build the command bound to a task.
     *
     * @param string|AbstractTask $task
     * @param string|null         $slug
     *
     * @return \Rocketeer\Abstracts\AbstractCommand
     */
    public function buildCommand($task, $slug = null)
    {
        // Build the task instance
        try {
            $instance = $this->buildTask($task);
        } catch (TaskCompositionException $exception) {
            $instance = null;
        }

        // Get the command name
        $name    = $instance ? $instance->getName() : null;
        $command = $this->findQualifiedName($name, [
            'Rocketeer\Console\Commands\%sCommand',
        ]);

        // If no command found, use BaseTaskCommand or task name
        if (!$command || $command === 'Closure') {
            $name    = is_string($task) ? $task : $name;
            $command = $this->findQualifiedName($name, [
                'Rocketeer\Console\Commands\%sCommand',
                'Rocketeer\Console\Commands\BaseTaskCommand',
            ]);
        }

        $command = new $command($instance, $slug);
        $command->setLaravel($this->app);

        return $command;
    }

    //////////////////////////////////////////////////////////////////////
    ///////////////////////////// STRATEGIES /////////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * Build a strategy.
     *
     * @param string      $strategy
     * @param string|null $concrete
     *
     * @return \Rocketeer\Abstracts\Strategies\AbstractStrategy|false
     */
    public function buildStrategy($strategy, $concrete = null)
    {
        // If we passed a concrete implementation
        // build it, otherwise get the bound one
        $handle = strtolower($strategy);
        if ($concrete) {
            $concrete = $this->findQualifiedName($concrete, [
                'Rocketeer\Strategies\\'.ucfirst($strategy).'\%sStrategy',
            ]);

            if (!$concrete) {
                return false;
            }

            return new $concrete($this->app);
        }

        return $this->app['rocketeer.strategies.'.$handle];
    }

    ////////////////////////////////////////////////////////////////////
    //////////////////////////////// TASKS /////////////////////////////
    ////////////////////////////////////////////////////////////////////

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
     * @param string|Closure|AbstractTask $task
     * @param string|null                 $name
     * @param string|null                 $description
     *
     * @throws \Rocketeer\Exceptions\TaskCompositionException
     *
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
     * Compose a Task from its various types.
     *
     * @param string|Closure|AbstractTask $task
     *
     * @throws \Rocketeer\Exceptions\TaskCompositionException
     *
     * @return mixed|AbstractTask
     */
    protected function composeTask($task)
    {
        // If already built, return it
        if ($task instanceof AbstractTask) {
            return $task;
        }

        // If we provided a Closure, build a ClosureTask
        if ($task instanceof Closure) {
            return $this->buildTaskFromClosure($task);
        }

        // If we passed a task handle, return it
        if ($handle = $this->getTaskHandle($task)) {
            return $this->app[$handle];
        }

        // If we passed a command, build a ClosureTask
        if (is_array($task) || $this->isStringCommand($task) || is_null($task)) {
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
     * Build a task from a Closure or a string command.
     *
     * @param Closure     $callback
     * @param string|null $stringTask
     *
     * @return AbstractTask
     */
    public function buildTaskFromClosure(Closure $callback, $stringTask = null)
    {
        /** @type \Rocketeer\Tasks\Closure $task */
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
     * Build a task from its name.
     *
     * @param string|AbstractTask $task
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
            throw new TaskCompositionException('Impossible to build task: '.$task);
        }

        return new $class($this->app);
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
        $handle = 'rocketeer.tasks.'.Str::snake($task, '-');
        $task   = $this->app->bound($handle) ? $handle : null;

        return $task;
    }

    /**
     * Check if a string is a command or a task.
     *
     * @param string|Closure|AbstractTask $string
     *
     * @return bool
     */
    protected function isStringCommand($string)
    {
        return is_string($string) && !$this->taskClassExists($string) && !$this->app->bound('rocketeer.tasks.'.$string);
    }

    /**
     * Check if a class with the given task name exists.
     *
     * @param string $task
     *
     * @return string|false
     */
    protected function taskClassExists($task)
    {
        return $this->findQualifiedName($task, [
            'Rocketeer\Tasks\%s',
            'Rocketeer\Tasks\Subtasks\%s',
        ]);
    }

    /**
     * Find a class in various predefined namespaces.
     *
     * @param string   $class
     * @param string[] $paths
     *
     * @return string|false
     */
    protected function findQualifiedName($class, $paths = [])
    {
        $paths[] = '%s';

        $class = ucfirst($class);
        foreach ($paths as $path) {
            $path = sprintf($path, $class);
            if (class_exists($path)) {
                return $path;
            }
        }

        return false;
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
