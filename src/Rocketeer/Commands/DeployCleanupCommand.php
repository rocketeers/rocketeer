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
	 * Execute the console command.
	 *
	 * @return void
	 */
	public function fire()
	{
		$maxReleases = $this->laravel['config']->get('rocketeer::remote.releases');

		// Create commands
		$trash = array_slice($this->getReleases(), $maxReleases);
		foreach ($trash as &$release) {
			$release = $this->removeFolder($this->getReleasesPath().'/'.$release);
		}

		// Remove old releases
		if (!empty($trash)) {
			$this->info('Removing '.sizeof($trash). ' releases from the server');
			$this->remote->run($trash);
		} else {
			$this->info('No releases to clean from the server');
		}
	}

	////////////////////////////////////////////////////////////////////
	/////////////////////////////// HELPERS ////////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Get the existing releases on the server
	 *
	 * @return array
	 */
	protected function getReleases()
	{
		$releases = array();

		$this->remote->run(array(
			'cd '.$this->getReleasesPath(),
			'ls',
		), function($folders, $remote) use (&$releases) {
			$releases = explode(PHP_EOL, $folders);
			$releases = array_filter($releases);
			rsort($releases);
		});

		return $releases;
	}

}