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

use DateTime;
use Rocketeer\Traits\Task;

/**
 * Display what the current release is
 *
 * @author Maxime Fabre <ehtnam6@gmail.com>
 */
class CurrentRelease extends Task
{
	 /**
	 * A description of what the Task does
	 *
	 * @var string
	 */
	protected $description = 'Display what the current release is';

	/**
	 * Run the Task
	 *
	 * @return  void
	 */
	public function execute()
	{
		// Get the current stage
		$stage = $this->rocketeer->getStage();
		$stage = $stage ? ' for stage '.$stage : '';

		// Check if a release has been deployed already
		$currentRelease = $this->releasesManager->getCurrentRelease();
		if (!$currentRelease) {
			return $this->command->error('No release has yet been deployed'.$stage);
		}

		// Create state message
		$date    = DateTime::createFromFormat('YmdHis', $currentRelease)->format('Y-m-d H:i:s');
		$state   = $this->runForCurrentRelease($this->scm->currentState());
		$message = sprintf('The current release' .$stage. ' is <info>%s</info> (<comment>%s</comment> deployed at <comment>%s</comment>)', $currentRelease, $state, $date);

		// Display current and past releases
		$this->command->line($message);
		$this->displayReleases();

		return $message;
	}
}
