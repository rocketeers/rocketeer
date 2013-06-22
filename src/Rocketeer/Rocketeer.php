<?php
namespace Rocketeer;

use Illuminate\Container\Container;
use Illuminate\Support\Str;

class Rocketeer
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
	///////////////////////////// APPLICATION //////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Get the name of the application to deploy
	 *
	 * @return string
	 */
	public function getApplicationName()
	{
		return $this->app['config']->get('rocketeer::remote.application_name');
	}

	/**
	 * Get the Git repository
	 *
	 * @return string
	 */
	public function getGitRepository()
	{
		// Get credentials
		$repository = $this->app['config']->get('rocketeer::git');
		$username   = $repository['username'];
		$password   = $repository['password'];
		$repository = $repository['repository'];

		// Add credentials if HTTPS
		if (str_contains($repository, 'https://'.$username)) {
			$repository = str_replace($username.'@', $username.':'.$password.'@', $repository);
		} else {
			$repository = str_replace('https://', 'https://'.$username.':'.$password.'@', $repository);
		}

		return $repository;
	}

	////////////////////////////////////////////////////////////////////
	//////////////////////////////// PATHS /////////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Get the path to a folder
	 *
	 * @param  strng $folder
	 *
	 * @return string
	 */
	public function getFolder($folder = null)
	{
		$base   = $this->getHomeFolder().'/';
		$folder = str_replace($base, null, $folder);

		return $base.$folder;
	}

	/**
	 * Get the path to the remote folder
	 *
	 * @return string
	 */
	public function getHomeFolder()
	{
		$rootDirectory = $this->app['config']->get('rocketeer::remote.root_directory');
		$rootDirectory = Str::finish($rootDirectory, '/');

		return $rootDirectory.$this->getApplicationName();
	}

}