<?php
/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Rocketeer;

use Illuminate\Container\Container;

/**
 * Provides informations and actions around releases
 *
 * @author Maxime Fabre <ehtnam6@gmail.com>
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
		// Get releases on server
		$releases = $this->app['rocketeer.bash']->listContents($this->getReleasesPath());
		if (is_array($releases)) {
			rsort($releases);
		}

		return $releases;
	}

	/**
	 * Get an array of deprecated releases
	 *
	 * @return array
	 */
	public function getDeprecatedReleases()
	{
		$releases    = (array) $this->getReleases();
		$maxReleases = $this->app['config']->get('rocketeer::remote.keep_releases');

		return array_slice($releases, $maxReleases);
	}

	/**
	 * Get an array of non-current releases
	 *
	 * @return array
	 */
	public function getNonCurrentReleases()
	{
		$releases = (array) $this->getReleases();

		return array_slice($releases, 1);
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
	 * Get the path to a release
	 *
	 * @param  integer $release
	 *
	 * @return string
	 */
	public function getPathToRelease($release)
	{
		return $this->app['rocketeer.rocketeer']->getFolder('releases/'.$release);
	}

	/**
	 * Get the path to the current release
	 *
	 * @param string $folder A folder in the release
	 *
	 * @return string
	 */
	public function getCurrentReleasePath($folder = null)
	{
		if ($folder) {
			$folder = '/'.$folder;
		}

		return $this->getPathToRelease($this->getCurrentRelease().$folder);
	}

	////////////////////////////////////////////////////////////////////
	/////////////////////////// CURRENT RELEASE ////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Get where to store the current release
	 *
	 * @return string
	 */
	protected function getCurrentReleaseKey()
	{
		$key = 'current_release';

		// Get the scopes
		$connection = $this->app['rocketeer.rocketeer']->getConnection();
		$stage      = $this->app['rocketeer.rocketeer']->getStage();
		$scopes     = array($connection, $stage);
		foreach ($scopes as $scope) {
			$key .= $scope ? '.'.$scope : '';
		}

		return $key;
	}

	/**
	 * Get the current release
	 *
	 * @return string
	 */
	public function getCurrentRelease()
	{
		// If we have saved the last deployed release, return that
		$cached = $this->app['rocketeer.server']->getValue($this->getCurrentReleaseKey());
		if ($cached) {
			return $cached;
		}

		// Else get and save last deployed release
		$lastDeployed = array_get($this->getReleases(), 0);
		$this->updateCurrentRelease($lastDeployed);

		return $lastDeployed;
	}

	/**
	 * Get the release before the current one
	 *
	 * @param string $release A release name
	 *
	 * @return string
	 */
	public function getPreviousRelease($release = null)
	{
		// Get all releases and the current one
		$releases = $this->getReleases();
		$current  = $release ?: $this->getCurrentRelease();

		// Get the one before that, or default to current
		$key     = array_search($current, $releases);
		$release = array_get($releases, $key + 1, $current);

		return $release;
	}

	/**
	 * Update the current release
	 *
	 * @param  string $release A release name
	 *
	 * @return void
	 */
	public function updateCurrentRelease($release = null)
	{
		if (!$release) {
			$release = date('YmdHis');
		}

		$this->app['rocketeer.server']->setValue($this->getCurrentReleaseKey(), $release);

		return $release;
	}
}
