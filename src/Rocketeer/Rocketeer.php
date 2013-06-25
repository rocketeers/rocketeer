<?php
namespace Rocketeer;

use Illuminate\Config\Repository;
use Illuminate\Support\Str;

/**
 * Handles interaction between the User provided informations
 * and the various Rocketeer components
 */
class Rocketeer
{

	/**
	 * The Config Repository
	 *
	 * @var Repository
	 */
	protected $config;

	/**
	 * The Rocketeer version
	 *
	 * @var string
	 */
	const VERSION = '0.4.0';

	/**
	 * Build a new ReleasesManager
	 *
	 * @param Repository $config
	 */
	public function __construct(Repository $config)
	{
		$this->config = $config;
	}

	/**
	 * Get an option from the config file
	 *
	 * @param  string $option
	 *
	 * @return mixed
	 */
	public function getOption($option)
	{
		return $this->config->get('rocketeer::'.$option);
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
		return $this->getOption('remote.application_name');
	}

	/**
	 * Get the Git repository
	 *
	 * @return string
	 */
	public function getGitRepository()
	{
		// Get credentials
		$repository = $this->getOption('git');
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

	/**
	 * Get the Git branch
	 *
	 * @return string
	 */
	public function getGitBranch()
	{
		return $this->getOption('git.branch');
	}

	/**
	 * Get an array of folders and files to share between releases
	 *
	 * @return array
	 */
	public function getShared()
	{
		return (array) $this->getOption('remote.shared');
	}

	////////////////////////////////////////////////////////////////////
	//////////////////////////////// PATHS /////////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Get the path to a folder
	 *
	 * @param  string $folder
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
		$rootDirectory = $this->getOption('remote.root_directory');
		$rootDirectory = Str::finish($rootDirectory, '/');

		return $rootDirectory.$this->getApplicationName();
	}

}
