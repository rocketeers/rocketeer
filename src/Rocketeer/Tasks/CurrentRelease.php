<?php
namespace Rocketeer\Tasks;

use Carbon\Carbon;

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
	public $description = 'Display what the current release is';

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
		$date    = Carbon::createFromTimestamp($currentRelease)->toDateTimeString();
		$message = sprintf('The current release is <info>%s</info> (deployed at <comment>%s</comment>)', $currentRelease, $date);

		return $this->command->line($message);
	}

}
