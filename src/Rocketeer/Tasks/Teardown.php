<?php
namespace Rocketeer\Tasks;

class Teardown extends Task
{

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
