<?php
namespace Rocketeer\Abstracts;

use Illuminate\Support\Str;
use Rocketeer\Bash;

/**
 * Core class for strategies
 */
abstract class AbstractStrategy extends Bash
{
	/**
	 * Whether this particular strategy is runnable or not
	 *
	 * @return boolean
	 */
	public function isExecutable()
	{
		return true;
	}

	//////////////////////////////////////////////////////////////////////
	////////////////////////////// HELPERS ///////////////////////////////
	//////////////////////////////////////////////////////////////////////

	/**
	 * @param string $hook
	 * @param array  $arguments
	 *
	 * @return bool|string
	 */
	protected function getHookedTasks($hook, array $arguments)
	{
		$tasks = $this->rocketeer->getOption($hook);
		if (!is_callable($tasks)) {
			return;
		}

		// Cancel if no tasks to execute
		$tasks = (array) call_user_func_array($tasks, $arguments);
		if (empty($tasks)) {
			return;
		}

		// Run commands
		return $tasks;
	}

	/**
	 * Display what the command is and does
	 *
	 * @return $this
	 */
	public function displayStatus()
	{
		// Recompose strategy and implementation from
		// the class name
		$components = get_class($this);
		$components = class_basename($components);
		$components = Str::snake($components);
		$components = explode('_', $components);

		$name     = array_get($components, 0);
		$strategy = array_get($components, 1);

		$object  = 'Running strategy for '.ucfirst($strategy);
		$subject = ucfirst($name);

		$this->explainer->display($object, $subject);

		return $this;
	}
}
