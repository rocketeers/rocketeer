<?php
namespace Rocketeer\Commands;

use DateTime;

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
			$this->error('No release has yet been deployed');
		} else {
			$date    = new DateTime('@'.$currentRelease);
			$message = sprintf('The current release is %s (deployed at %s)', $currentRelease, $date->format('Y-m-d H:i:s'));

			$this->comment($message);
		}
	}

}
