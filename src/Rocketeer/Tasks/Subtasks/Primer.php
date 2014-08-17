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
	 * A description of what the task does
	 *
	 * @var string
	 */
	protected $description = 'Run local checks to ensure deploy can proceed';

	/**
	 * Whether to run the commands locally
	 * or on the server
	 *
	 * @type boolean
	 */
	protected $local = true;

	/**
	 * Whether the task needs to be run on each stage or globally
	 *
	 * @var boolean
	 */
	public $usesStages = false;

	/**
	 * Run the task
	 *
	 * @return boolean
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
