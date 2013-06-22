<?php
namespace Rocketeer;

use Illuminate\Filesystem\Filesystem;

/**
 * Handles the Deployments repository that stores static data
 * about the state of the remote servers
 */
class DeploymentsManager
{

	/**
	 * The Filesystem instance
	 *
	 * @var Filesystem
	 */
	protected $app;

	/**
	 * The path to the deployments file
	 *
	 * @var string
	 */
	protected $deploymentsFilepath;

	/**
	 * Build a new ReleasesManager
	 *
	 * @param Filesystem $files
	 * @param string     $storage Path to the storage folder
	 */
	public function __construct(Filesystem $files, $storage)
	{
		$this->files               = $files;
		$this->deploymentsFilepath = $storage.'/meta/deployments.json';
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
		array_set($deployments, $key, $value);

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
		$this->files->put($this->deploymentsFilepath, json_encode($data));
	}

	/**
	 * Get the contents of the deployments file
	 *
	 * @return array
	 */
	protected function getDeploymentsFile()
	{
		// Cancel if the file doesn't exist
		if (!$this->files->exists($this->deploymentsFilepath)) {
			return null;
		}

		// Get and parse file
		$deployments = $this->files->get($this->deploymentsFilepath);
		$deployments = json_decode($deployments, true);

		return $deployments;
	}

}
