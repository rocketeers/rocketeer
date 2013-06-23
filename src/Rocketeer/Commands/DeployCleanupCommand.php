<?php
namespace Rocketeer\Commands;

/**
 * Clean up old releases from the server
 */
class DeployCleanupCommand extends BaseDeployCommand
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
			$release = $this->removeFolder($this->getReleasesManager()->getPathToRelease($release));
		}

		// If no releases to prune
		if (empty($trash)) {
			$this->info('No releases to prune from the server');
			return array();
		}

		$this->info('Removing '.sizeof($trash). ' releases from the server');
		return $trash;
	}

}
