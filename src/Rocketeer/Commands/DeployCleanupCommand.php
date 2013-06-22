<?php
namespace Rocketeer\Commands;

class DeployCleanupCommand extends DeployCommand
{

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'deploy:cleanup';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Clean up old releases from the server';

	/**
	 * The tasks to execute
	 *
	 * @return array
	 */
	protected function tasks()
	{
		// Get deprecated releases and create commands
		$trash = $this->getReleasesManager()->getDeprecatedReleases();
		foreach ($trash as &$release) {
			$release = $this->removeFolder($this->getReleasesPath().'/'.$release);
		}

		if (empty($trash)) {
			$this->info('No releases to prune from the server');
			return array();
		} else {
			$this->info('Removing '.sizeof($trash). ' releases from the server');
			return $trash;
		}
	}

}