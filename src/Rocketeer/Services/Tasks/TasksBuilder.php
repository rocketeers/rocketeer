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

	//////////////////////////////////////////////////////////////////////
	///////////////////////////// STRATEGIES /////////////////////////////
	//////////////////////////////////////////////////////////////////////

	/**
	 * Build a strategy
	 *
	 * @param string      $strategy
	 * @param string|null $concrete
	 *
	 * @return \Rocketeer\Abstracts\Strategies\AbstractStrategy
	 */
	public function buildStrategy($strategy, $concrete = null)
	{
		// If we passed a concrete implementation
		// build it, otherwise get the bound one
		$handle = strtolower($strategy);
		if ($concrete) {
			$concrete = $this->findQualifiedName($concrete, array(
				'Rocketeer\Strategies\\'.ucfirst($strategy).'\%sStrategy',
			));
			if (!$concrete) {
				return false;
			}

			return new $concrete($this->app);
		}

		return $this->app['rocketeer.strategies.'.$handle];
	}

	////////////////////////////////////////////////////////////////////
	/////////////////////////////// BUILDING ///////////////////////////
	////////////////////////////////////////////////////////////////////

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
		// Check the handle if possible
		if (is_string($task)) {
			$handle = 'rocketeer.tasks.'.Str::snake($task, '-');
		}

		if ($task instanceof Closure) {
			// If we provided a Closure, build a ClosureTask
			$task = $this->buildTaskFromClosure($task);
		} elseif (isset($handle) and $this->app->bound($handle)) {
			// If we passed a task handle, return it
			$task = $this->app[$handle];
		} elseif (is_array($task) or $this->isStringCommand($task)) {
			// If we passed a command, build a ClosureTask
			$task = $this->buildTaskFromString($task);
		} elseif (!$task instanceof AbstractTask) {
			// Else it's a class name, get the appropriated task
			$task = $this->buildTaskFromClass($task);
		}

		// If the built class is invalid, cancel
		if (!$task instanceof AbstractTask) {
			throw new TaskCompositionException('Class '.get_class($task).' is not a valid task');
		}

		// Set task properties
		$task->setName($name);
		$task->setDescription($description);

		return $task;
	}

	/**
	 * Build a task from a string
	 *
	 * @param string $task
	 *
	 * @return AbstractTask
	 */
	public function buildTaskFromString($task)
	{
		$stringTask = $task;
		$closure    = function ($task) use ($stringTask) {
			return $task->runForCurrentRelease($stringTask);
		};

		return $this->buildTaskFromClosure($closure, $stringTask);
	}

	/**
	 * Build a task from a Closure or a string command
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
	 * Build a task from its name
	 *
	 * @param string|AbstractTask $task
	 *
	 * @throws TaskCompositionException
	 * @return AbstractTask
	 */
	public function buildTaskFromClass($task)
	{
		if (is_object($task) and $task instanceof AbstractTask) {
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
	 * Check if a string is a command or a task
	 *
	 * @param AbstractTask|Closure|string $string
	 *
	 * @return boolean
	 */
	protected function isStringCommand($string)
	{
		return is_string($string) && !$this->taskClassExists($string) && !$this->app->bound('rocketeer.tasks.'.$string);
	}

	/**
	 * Check if a class with the given task name exists
	 *
	 * @param string $task
	 *
	 * @return string|false
	 */
	protected function taskClassExists($task)
	{
		return $this->findQualifiedName($task, array(
			'Rocketeer\Tasks\%s',
			'Rocketeer\Tasks\Subtasks\%s',
		));
	}

	/**
	 * Find a class in various predefined namespaces
	 *
	 * @param string $class
	 * @param array  $paths
	 *
	 * @return string|false
	 */
	protected function findQualifiedName($class, $paths = array())
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
}
