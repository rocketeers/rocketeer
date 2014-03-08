<?php
/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Rocketeer\Tasks;

use Rocketeer\Traits\Task;

/**
 * Set up the remote server for deployment
 *
 * @author Maxime Fabre <ehtnam6@gmail.com>
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
		if ($this->executeTask('Check') === false and !$this->getOption('pretend')) {
			return false;
		}

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
		$message     = sprintf('Successfully setup "%s" at "%s"', $application, $homeFolder);

		return $this->command->info($message);
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
		$availableStages = $this->rocketeer->getStages();
		$originalStage   = $this->rocketeer->getStage();
		if (empty($availableStages)) {
			$availableStages = array(null);
		}

		// Create folders
		foreach ($availableStages as $stage) {
			$this->rocketeer->setStage($stage);
			$this->createFolder('releases', true);
			$this->createFolder('current', true);
			$this->createFolder('shared', true);
		}

		if ($originalStage) {
			$this->rocketeer->setStage($originalStage);
		}
	}
}
