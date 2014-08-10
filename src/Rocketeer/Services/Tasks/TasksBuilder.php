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
use Rocketeer\Abstracts\AbstractTask;
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
	 *
	 * @return AbstractTask
	 */
	public function buildTask($task, $name = null)
	{
		// Check the handle if possible
		if (is_string($task)) {
			$handle = 'rocketeer.tasks.'.$task;
		}

		// If we provided a Closure or a string command, build it
		if ($task instanceof Closure or $this->isStringCommand($task)) {
			$task = $this->buildTaskFromClosure($task);
		} // Check for an existing container binding
		elseif (isset($handle) and $this->app->bound($handle)) {
			return $this->app[$handle];
		}

		// Build remaining tasks
		if (!$task instanceof AbstractTask) {
			$task = $this->buildTaskFromClass($task);
		}

		// Set the task's name
		if ($name) {
			$task->setName($name);
		}

		return $task;
	}

	/**
	 * Build a task from a Closure or a string command
	 *
	 * @param Closure|string $task
	 *
	 * @return AbstractTask
	 */
	public function buildTaskFromClosure($task)
	{
		// If the User provided a string to execute
		// We'll build a closure from it
		if ($this->isStringCommand($task)) {
			$stringTask = $task;
			$closure    = function ($task) use ($stringTask) {
				return $task->runForCurrentRelease($stringTask);
			};
			// If the User provided a Closure
		} else {
			$closure = $task;
		}

		// Now that we unified it all to a Closure, we build
		// a Closure AbstractTask from there
		$task = $this->buildTaskFromClass('Rocketeer\Tasks\Closure');
		$task->setClosure($closure);

		// If we had an original string used, store it on
		// the task for easier reflection
		if (isset($stringTask)) {
			$task->setStringTask($stringTask);
		}

		return $task;
	}

	/**
	 * Build a task from its name
	 *
	 * @param string|AbstractTask $task
	 *
	 * @return AbstractTask|string
	 */
	public function buildTaskFromClass($task)
	{
		if (is_object($task) and $task instanceof AbstractTask) {
			return $task;
		}

		// Shortcut for calling Rocketeer Tasks
		if (class_exists('Rocketeer\Tasks\\'.ucfirst($task))) {
			$task = 'Rocketeer\Tasks\\'.ucfirst($task);
		}

		// Cancel if class doesn't exist
		if (!class_exists($task)) {
			return $task;
		}

		return new $task($this->app);
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
		return is_string($string) && !class_exists($string) && !$this->app->bound('rocketeer.tasks.'.$string);
	}
}
