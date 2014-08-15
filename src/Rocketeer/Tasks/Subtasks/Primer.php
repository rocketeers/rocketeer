<?php
namespace Rocketeer\Tasks\Subtasks;

use Rocketeer\Abstracts\AbstractTask;

/**
 * Executes some sanity-check commands before deploy
 *
 * @author Maxime Fabre <ehtnam6@gmail.com>
 */
class Primer extends AbstractTask
{
	/**
	 * Whether to run the commands locally
	 * or on the server
	 *
	 * @type boolean
	 */
	protected $local = true;

	/**
	 * Run the task
	 *
	 * @return string
	 */
	public function execute()
	{
		$tasks = $this->getHookedTasks('primer', [$this]);
		if (!$tasks) {
			return true;
		}

		$this->run($tasks);

		return $this->status();
	}
}
