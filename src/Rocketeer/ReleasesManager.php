<?php
namespace Rocketeer;

use Illuminate\Container\Container;

/**
 * Handles the managing and cleaning of releases
 */
class ReleasesManager
{

	/**
	 * The IoC Container
	 *
	 * @var Container
	 */
	protected $app;

	/**
	 * Build a new ReleasesManager
	 *
	 * @param Container $app
	 */
	public function __construct(Container $app)
	{
		$this->app = $app;
	}

	////////////////////////////////////////////////////////////////////
	/////////////////////////////// RELEASES ///////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Get all the releases on the server
	 *
	 * @return array
	 */
	public function getReleases()
	{
		$releases = array();

		$this->app['remote']->run(array(
			'cd '.$this->getReleasesPath(),
			'ls',
		), function($folders, $remote) use (&$releases) {
			$releases = explode(PHP_EOL, $folders);
			$releases = array_filter($releases);
			rsort($releases);
		});

		return $releases;
	}

	/**
	 * Get an array of deprecated releases
	 *
	 * @return array
	 */
	public function getDeprecatedReleases()
	{
		$releases    = $this->getReleases();
		$maxReleases = $this->app['config']->get('rocketeer::remote.keep_releases');

		return array_slice($releases, $maxReleases);
	}

	////////////////////////////////////////////////////////////////////
	////////////////////////////// PATHS ///////////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Get the path to the releases folder
	 *
	 * @return string
	 */
	public function getReleasesPath()
	{
		return $this->app['rocketeer.rocketeer']->getFolder('releases');
	}

	/**
	 * Get the path to the current release
	 *
	 * @return string
	 */
	public function getCurrentReleasePath()
	{
		return $this->getReleasesPath().'/'.$this->getCurrentRelease();
	}

	////////////////////////////////////////////////////////////////////
	/////////////////////////// CURRENT RELEASE ////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Get the path to the current release
	 *
	 * @return string
	 */
	public function getCurrentRelease()
	{
		return $this->app['rocketeer.deployments']->getValue('current_release');
	}

	/**
	 * Update the current release
	 *
	 * @param  string $release
	 *
	 * @return void
	 */
	public function updateCurrentRelease($release)
	{
		$this->app['rocketeer.deployments']->setValue('current_release', $release);
	}

}
