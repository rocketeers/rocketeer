<?php
namespace Rocketeer\Tasks;

class Setup extends Task
{

	/**
	 * Run the Task
	 *
	 * @return  void
	 */
	public function execute()
	{
		// Remove existing installation
		$this->removeFolder();

		// Create base folder and subfolders
		$this->createFolder();
		$this->createFolder('releases');
		$this->createFolder('current');

		// Set setup to true
		$this->deploymentsManager->setValue('is_setup', true);

		// Create confirmation message
		$application = $this->rocketeer->getApplicationName();
		$homeFolder  = $this->rocketeer->getHomeFolder();

		$this->command->info(sprintf('Successfully setup "%s" at "%s"', $application, $homeFolder));
	}

}