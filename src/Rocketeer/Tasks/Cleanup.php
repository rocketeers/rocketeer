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

use Illuminate\Support\Str;
use Rocketeer\Abstracts\AbstractTask;
use Rocketeer\Services\Storages\ServerStorage;

/**
 * Clean up old releases from the server
 *
 * @author Maxime Fabre <ehtnam6@gmail.com>
 */
class Cleanup extends AbstractTask
{
	/**
	 * A description of what the task does
	 *
	 * @var string
	 */
	protected $description = 'Clean up old releases from the server';

	/**
	 * Run the task
	 */
	public function execute()
	{
		// If no releases to prune
		if (!$trash = $this->getReleasesToCleanup()) {
			return $this->explainer->line('No releases to prune from the server');
		}

		// Prune releases
		$trash = array_map([$this->releasesManager, 'getPathToRelease'], $trash);
		$this->removeFolder($trash);

		// Create final message
		$trash   = count($trash);
		$message = sprintf('Removing <info>%d %s</info> from the server', $trash, Str::plural('release', $trash));

		// Delete state file
		if ($this->getOption('clean-all')) {
			$state = new ServerStorage($this->app, 'state');
			$state->destroy();
			$this->releasesManager->markReleaseAsValid();
		}

		return $this->explainer->line($message);
	}

	/**
	 * Get an array of releases to prune
	 *
	 * @return integer[]
	 */
	protected function getReleasesToCleanup()
	{
		return $this->getOption('clean-all')
			? $this->releasesManager->getNonCurrentReleases()
			: $this->releasesManager->getDeprecatedReleases();
	}
}
