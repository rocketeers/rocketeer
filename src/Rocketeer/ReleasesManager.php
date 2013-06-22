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
		$maxReleases = $this->app['config']->get('rocketeer::remote.releases');
		$releases    = $this->app['rocketeer.releases']->getReleases();

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
		$deployments = $this->getDeploymentsFile();

		return $deployments ? $deployments['current_release'] : 0;
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
		$this->updateDeploymentsFile(array(
			'current_release' => $release,
		));
	}

	////////////////////////////////////////////////////////////////////
	/////////////////////////// DEPLOYMENTS FILE ///////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Update the deployments file
	 *
	 * @param array $data
	 *
	 * @return void
	 */
	protected function updateDeploymentsFile($data)
	{
		$this->app['files']->put($this->getDeploymentsFilePath(), json_encode($data));
	}

	/**
	 * Get the contents of the deployments file
	 *
	 * @return array
	 */
	protected function getDeploymentsFile()
	{
		$deployments = $this->getDeploymentsFilePath();
		if (!file_exists($deployments)) return null;

		// Get and parse file
		$deployments = $this->app['files']->get($deployments);
		$deployments = json_decode($deployments, true);

		return $deployments;
	}

	/**
	 * Get the path to the deployments file
	 *
	 * @return string
	 */
	protected function getDeploymentsFilePath()
	{
		return $this->app->make('path.storage').'/meta/deployments.json';
	}

}