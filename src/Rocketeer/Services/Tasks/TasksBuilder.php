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
	 * @param Closure     $closure
	 * @param string|null $stringTask
	 *
	 * @return AbstractTask
	 */
	public function buildTaskFromClosure(Closure $closure, $stringTask = null)
	{
		/** @type \Rocketeer\Tasks\Closure $task */
		$task = $this->buildTaskFromClass('Rocketeer\Tasks\Closure');
		$task->setClosure($closure);

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
		$class = ucfirst($task);
		if (class_exists('Rocketeer\Tasks\\'.$class)) {
			return 'Rocketeer\Tasks\\'.$class;
		} elseif (class_exists('Rocketeer\Tasks\Subtasks\\'.$class)) {
			return 'Rocketeer\Tasks\Subtasks\\'.$class;
		} elseif (class_exists($task)) {
			return $task;
		}

		return false;
	}
}
