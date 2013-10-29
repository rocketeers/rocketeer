<?php
namespace Rocketeer;

use Exception;
use Illuminate\Container\Container;

/**
 * Provides and persists informations about the remote server
 */
class Server
{
	/**
	 * The IoC Container
	 *
	 * @var Container
	 */
	protected $app;

	/**
	 * The path to the storage file
	 *
	 * @var string
	 */
	protected $repository;

	/**
	 * Build a new ReleasesManager
	 *
	 * @param Container  $app
	 * @param string     $storage Path to the storage folder
	 */
	public function __construct(Container $app, $storage = null)
	{
		$this->app = $app;

		// Create personnal storage if necessary
		if (!$app->bound('path.storage') and !$storage) {
			$storage = __DIR__.DS.'..'.DS.'..'.DS.'storage';
			@mkdir($storage);
		}

		// Get correct storage path
		$storage = $storage ?: $app['path.storage'].DS.'meta';
		$this->repository = $storage.DS.'deployments.json';
	}

	////////////////////////////////////////////////////////////////////
	/////////////////////////// REMOTE VARIABLES ///////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Get the directory separators on the remove server
	 *
	 * @return string
	 */
	public function getSeparator()
	{
		// If manually set by the user, return it
		$user = $this->app['rocketeer.rocketeer']->getOption('remote.variables.directory_separator');
		if ($user) {
			return $user;
		}

		$bash = $this->app['rocketeer.bash'];
		return $this->getValue('directory_separator', function ($server) use ($bash) {
			$separator = $bash->runRaw('php -r "echo DIRECTORY_SEPARATOR;"');

			// Throw an Exception if we receive invalid output
			if (strlen($separator) > 1) {
				throw new Exception(
					'An error occured while fetching the directory separators used on the server.'.PHP_EOL.
					'Output received was : '.$separator
				);
			}

			// Cache separator
			$server->setValue('directory_separator', $separator);

			return $separator;
		});
	}

	/**
	 * Get the remote line endings on the remove server
	 *
	 * @return string
	 */
	public function getLineEndings()
	{
		// If manually set by the user, return it
		$user = $this->app['rocketeer.rocketeer']->getOption('remote.variables.line_endings');
		if ($user) {
			return $user;
		}

		$bash = $this->app['rocketeer.bash'];
		return $this->getValue('line_endings', function ($server) use ($bash) {
			$endings = $bash->runRaw('php -r "echo PHP_EOL;"');
			$server->setValue('line_endings', $endings);

			return $endings;
		});
	}

	////////////////////////////////////////////////////////////////////
	/////////////////////////////// KEYSTORE ///////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Get a value from the repository file
	 *
	 * @param  string          $key
	 * @param  \Closure|string $fallback
	 *
	 * @return mixed
	 */
	public function getValue($key, $fallback = null)
	{
		$value = array_get($this->getRepository(), $key, null);

		// Get fallback value
		if (is_null($value)) {
			return is_callable($fallback) ? $fallback($this) : $fallback;
		}

		return $value;
	}

	/**
	 * Set a value from the repository file
	 *
	 * @param string $key
	 * @param mixed  $value
	 *
	 * @return array
	 */
	public function setValue($key, $value)
	{
		$repository = $this->getRepository();
		array_set($repository, $key, $value);

		return $this->updateRepository($repository);
	}

	/**
	 * Forget a value from the repository file
	 *
	 * @param  string $key
	 *
	 * @return array
	 */
	public function forgetValue($key)
	{
		$repository = $this->getRepository();
		array_forget($repository, $key);

		return $this->updateRepository($repository);
	}

	////////////////////////////////////////////////////////////////////
	////////////////////////// REPOSITORY FILE /////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Replace the contents of the deployments file
	 *
	 * @param array $data
	 *
	 * @return array
	 */
	public function updateRepository($data)
	{
		$this->app['files']->put($this->repository, json_encode($data));

		return $data;
	}

	/**
	 * Get the contents of the deployments file
	 *
	 * @return array
	 */
	public function getRepository()
	{
		// Cancel if the file doesn't exist
		if (!$this->app['files']->exists($this->repository)) {
			return array();
		}

		// Get and parse file
		$repository = $this->app['files']->get($this->repository);
		$repository = json_decode($repository, true);

		return $repository;
	}

	/**
	 * Deletes the deployments file
	 *
	 * @return boolean
	 */
	public function deleteRepository()
	{
		return $this->app['files']->delete($this->repository);
	}
}
