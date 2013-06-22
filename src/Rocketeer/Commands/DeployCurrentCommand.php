<?php
namespace Rocketeer\Commands;

use Carbon\Carbon;

class DeployCurrentCommand extends BaseDeployCommand
{

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'deploy:current';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Displays what the current release is';

	/**
	 * The tasks to execute
	 *
	 * @return array
	 */
	public function fire()
	{
		$currentRelease = $this->getReleasesManager()->getCurrentRelease();
		if (!$currentRelease) {
			return $this->error('No release has yet been deployed');
		}

		// Create message
		$date    = Carbon::createFromTimestamp($currentRelease)->toDateTimeString();
		$message = sprintf('The current release is <info>%s</info> (deployed at <comment>%s</comment>)', $currentRelease, $date);

		$this->line($message);
	}

}
