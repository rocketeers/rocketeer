<?php
namespace Rocketeer\Tasks;

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
	public $description = 'Remove the remote applications and existing caches';

	/**
	 * Run the Task
	 *
	 * @return  void
	 */
	public function execute()
	{
		// Remove remote folders
		$this->removeFolder();

		// Remove deployments file
		$this->deploymentsManager->deleteDeploymentsFile();

		$this->command->info('The application was successfully removed from the remote servers');
	}

}
