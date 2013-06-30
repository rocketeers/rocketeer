<?php
namespace Rocketeer\Tasks;

use Rocketeer\Tasks\Abstracts\Task;

/**
 * Set up the remote server for deployment
 */
class Setup extends Task
{

	 /**
	 * A description of what the Task does
	 *
	 * @var string
	 */
	protected $description = 'Set up the remote server for deployment';

	/**
	 * Run the Task
	 *
	 * @return  void
	 */
	public function execute()
	{
		// Check if requirments are met
		if (!$this->executeTask('Check')) {
			return false;
		}

		// Remove existing installation
		$this->executeTask('Teardown');

		// Create base folder and subfolders
		$this->createFolder();
		$this->createFolder('releases');
		$this->createFolder('current');
		$this->createFolder('shared');

		// Set setup to true
		$this->deploymentsManager->setValue('is_setup', true);

		// Create confirmation message
		$application = $this->rocketeer->getApplicationName();
		$homeFolder  = $this->rocketeer->getHomeFolder();

		return $this->command->info(sprintf('Successfully setup "%s" at "%s"', $application, $homeFolder));
	}

}
