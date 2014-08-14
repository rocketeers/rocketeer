<?php
namespace Rocketeer\Abstracts\Strategies;

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
	 * @return string|array|null
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
		$components = explode('\\', $components);

		$name     = array_get($components, sizeof($components) - 1);
		$strategy = array_get($components, sizeof($components) - 2);

		$object  = 'Running strategy for '.ucfirst($strategy);
		$subject = str_replace('Strategy', null, $name);

		$this->explainer->display($object, $subject);

		return $this;
	}
}
