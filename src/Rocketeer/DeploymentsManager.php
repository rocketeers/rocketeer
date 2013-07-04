<?php
namespace Rocketeer;

use Illuminate\Container\Container;

/**
 * Handles the Deployments repository that stores static data
 * about the state of the remote servers
 */
class DeploymentsManager
{

	/**
	 * The IoC Container
	 *
	 * @var Container
	 */
	protected $app;

	/**
	 * The Filesystem instance
	 *
	 * @var Filesystem
	 */
	protected $files;

	/**
	 * The path to the deployments file
	 *
	 * @var string
	 */
	protected $deploymentsFilepath;

	/**
	 * Build a new ReleasesManager
	 *
	 * @param Container  $app
	 * @param string     $storage Path to the storage folder
	 */
	public function __construct(Container $app, $storage)
	{
		$this->app                 = $app;
		$this->files               = $app['files'];
		$this->deploymentsFilepath = $storage.'/meta/deployments.json';
	}

	////////////////////////////////////////////////////////////////////
	/////////////////////////// REMOTE VARIABLES ///////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Get the directory separators
	 *
	 * @return string
	 */
	public function getSeparator()
	{
		return $this->getValue('directory_separator', function() {
			$separator = $this->app['rocketeer.bash']->runRemoteCommands('php -r "echo DIRECTORY_SEPARATOR;"');
			$this->setValue('directory_separator', $separator);

			return $separator;
		});
	}

	/**
	 * Get the remote line endings
	 *
	 * @return string
	 */
	public function getLineEndings()
	{
		return $this->getValue('line_endings', function() {
			$endings = $this->app['rocketeer.bash']->runRemoteCommands('php -r "echo PHP_EOL;"');
			$this->setValue('line_endings', $endings);

			return $endings;
		});
	}

	////////////////////////////////////////////////////////////////////
	/////////////////////////////// KEYSTORE ///////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Get a value from the deployments file
	 *
	 * @param  string         $key
	 * @param  Closure|string $fallback
	 *
	 * @return mixed
	 */
	public function getValue($key, $fallback = null)
	{
		$value = array_get($this->getDeploymentsFile(), $key, null);

		// Get fallback value
		if (is_null($value)) {
			return is_callable($fallback) ? $fallback() : $fallback;
		}

		return $value;
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
			return array();
		}

		// Get and parse file
		$deployments = $this->files->get($this->deploymentsFilepath);
		$deployments = json_decode($deployments, true);

		return $deployments;
	}

	/**
	 * Deletes the deployments file
	 *
	 * @return boolean
	 */
	public function deleteDeploymentsFile()
	{
		return $this->files->delete($this->deploymentsFilepath);
	}

}
