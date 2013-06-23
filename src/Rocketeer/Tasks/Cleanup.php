<?php
namespace Rocketeer\Tasks;

class Cleanup extends Task
{

	/**
	 * Run the Task
	 *
	 * @return  void
	 */
	public function execute()
	{
		// Get deprecated releases and create commands
		$trash = $this->releasesManager->getDeprecatedReleases();
		foreach ($trash as $release) {
			$this->removeFolder($this->releasesManager->getPathToRelease($release));
		}

		// If no releases to prune
		if (empty($trash)) {
			return $this->command->info('No releases to prune from the server');
		}

		// Create final message
		$trash   = sizeof($trash);
		$message = sprintf('Removing %d %s from the server', $trash, Str::plural('release', $trash));

		return $this->command->info($message);
	}

}