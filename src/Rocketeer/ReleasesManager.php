<?php
namespace Rocketeer;

use Illuminate\Container\Container;

/**
 * Provides informations and actions around releases
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
		rsort($releases);

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
	 * Get the current release
	 *
	 * @return string
	 */
	public function getCurrentRelease()
	{
		return $this->app['rocketeer.server']->getValue('current_release');
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
	public function updateCurrentRelease($release)
	{
		$this->app['rocketeer.server']->setValue('current_release', $release);
	}
}
