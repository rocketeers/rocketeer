<?php
namespace Rocketeer\Tasks;

use Rocketeer\Traits\Task;

/**
 * Remove the remote applications and existing caches
 */
class Teardown extends Task
{
	 /**
	 * A description of what the Task does
	 *
	 * @var string
	 */
	protected $description = 'Remove the remote applications and existing caches';

	/**
	 * Whether the Task needs to be run on each stage or globally
	 *
	 * @var boolean
	 */
	public $usesStages = false;

	/**
	 * Run the Task
	 *
	 * @return  void
	 */
	public function execute()
	{
		// Ask confirmation
		$confirm = $this->command->confirm('This will remove all folders on the server, not just releases. Do you want to proceed ?');
		if (!$confirm) {
			return $this->command->info('Teardown aborted');
		}

		// Remove remote folders
		$this->removeFolder();

		// Remove deployments file
		$this->server->deleteRepository();

		$this->command->info('The application was successfully removed from the remote servers');

		return $this->history;
	}
}
