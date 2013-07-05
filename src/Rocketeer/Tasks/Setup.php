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
		// Check if requirments are met
		if (!$this->executeTask('Check')) {
			return false;
		}

		// Remove existing installation
		$this->executeTask('Teardown');

		// Create base folder
		$this->createFolder();
		$this->createStages();

		// Set setup to true
		$this->server->setValue('is_setup', true);

		// Get server informations
		$this->command->comment('Getting some informations about the server');
		$this->server->getSeparator();
		$this->server->getLineEndings();

		// Create confirmation message
		$application = $this->rocketeer->getApplicationName();
		$homeFolder  = $this->rocketeer->getHomeFolder();

		return $this->command->info(sprintf('Successfully setup "%s" at "%s"', $application, $homeFolder));
	}

	////////////////////////////////////////////////////////////////////
	/////////////////////////////// HELPERS ////////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Create the Application's folders
	 *
	 * @return void
	 */
	protected function createStages()
	{
		// Get stages
		$stages = $this->rocketeer->getStages();
		if (empty($stages)) $stages = array(null);

		// Create folders
		foreach ($stages as $stage) {
			$this->rocketeer->setStage($stage);
			$this->createFolder('releases', true);
			$this->createFolder('current', true);
			$this->createFolder('shared', true);
		}
	}

}
