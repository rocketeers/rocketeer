<?php
namespace Rocketeer;

use Illuminate\Filesystem\Filesystem;

class DeploymentsManager
{

	/**
	 * The Filesystem instance
	 *
	 * @var Filesystem
	 */
	protected $app;

	/**
	 * Build a new ReleasesManager
	 *
	 * @param Container $app
	 */
	public function __construct(Filesystem $files)
	{
		$this->files = $files;
	}

	////////////////////////////////////////////////////////////////////
	/////////////////////////////// KEYSTORE ///////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Get a value from the deployments file
	 *
	 * @param  string $key
	 *
	 * @return mixed
	 */
	public function getValue($key)
	{
		return array_get($this->getDeploymentsFile(), $key, false);
	}

	/**
	 * Set a value from the deployments file
	 *
	 * @param string $key
	 * @param mixed $value
	 */
	public function setValue($key, $value)
	{
		$deployments = $this->getDeploymentsFile();
		$deployments[$key] = $value;

		$this->updateDeploymentsFile($deployments);
	}

	////////////////////////////////////////////////////////////////////
	////////////////////////// DEPLOYMENTS FILE ////////////////////////
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
		$this->files->put($this->getDeploymentsFilePath(), json_encode($data));
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
		$deployments = $this->files->get($deployments);
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
