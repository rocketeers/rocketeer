<?php
namespace Rocketeer\Tasks;

use Carbon\Carbon;
use Rocketeer\Traits\Task;

/**
 * Display what the current release is
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
		// Check if a release has been deployed already
		$currentRelease = $this->releasesManager->getCurrentRelease();
		if (!$currentRelease) {
			return $this->command->error('No release has yet been deployed');
		}

		// Create message
		$date    = Carbon::createFromFormat('YmdHis', $currentRelease)->toDateTimeString();
		$state   = $this->runForCurrentRelease($this->scm->currentState());
		$message = sprintf('The current release is <info>%s</info> (<comment>%s</comment> deployed at <comment>%s</comment>)', $currentRelease, $state, $date);

		return $this->command->line($message);
	}
}
