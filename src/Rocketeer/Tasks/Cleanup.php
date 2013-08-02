<?php
namespace Rocketeer\Tasks;

use Illuminate\Support\Str;
use Rocketeer\Traits\Task;

/**
 * Clean up old releases from the server
 */
class Cleanup extends Task
{
	 /**
	 * A description of what the Task does
	 *
	 * @var string
	 */
	protected $description = 'Clean up old releases from the server';

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
			return $this->command->comment('No releases to prune from the server');
		}

		// Create final message
		$trash   = sizeof($trash);
		$message = sprintf('Removing <info>%d %s</info> from the server', $trash, Str::plural('release', $trash));

		return $this->command->line($message);
	}
}
