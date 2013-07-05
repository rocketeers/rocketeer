<?php
namespace Rocketeer;

use Illuminate\Container\Container;
use Illuminate\Support\Str;

/**
 * Handles interaction between the User provided informations
 * and the various Rocketeer components
 */
class Rocketeer
{

	/**
	 * The IoC Container
	 *
	 * @var Container
	 */
	protected $app;

	/**
	 * The current stage
	 *
	 * @var string
	 */
	protected $stage;

	/**
	 * The Rocketeer version
	 *
	 * @var string
	 */
	const VERSION = '0.5.0';

	/**
	 * Build a new ReleasesManager
	 *
	 * @param Container $app
	 */
	public function __construct(Container $app)
	{
		$this->app = $app;
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
		return $this->app['config']->get('rocketeer::'.$option);
	}

	////////////////////////////////////////////////////////////////////
	//////////////////////////////// STAGES ////////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Execute the Task on a particular stage
	 *
	 * @param  string $stage
	 *
	 * @return void
	 */
	public function setStage($stage)
	{
		$this->stage = $stage;
	}

	/**
	 * Get the current stage
	 *
	 * @return string
	 */
	public function getStage()
	{
		return $this->stage;
	}

	/**
	 * Get the stages of the application
	 *
	 * @return array
	 */
	public function getStages()
	{
		return $this->getOption('stages.stages');
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
	 * Get an array of folders and files to share between releases
	 *
	 * @return array
	 */
	public function getShared()
	{
		// Get path to logs
		$base = $this->app['path.base'];
		$logs = $this->app['path.storage'].'/logs';
		$logs = str_replace($base, null, $logs);

		// Add logs to shared folders
		$shared = (array) $this->getOption('remote.shared');
		$shared[] = trim($logs, '/');

		return $shared;
	}

	////////////////////////////////////////////////////////////////////
	/////////////////////////// GIT REPOSITORY /////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Whether the repository used is using SSH or HTTPS
	 *
	 * @return boolean
	 */
	public function usesSsh()
	{
		return Str::contains($this->getOption('scm.repository'), 'git@');
	}

	/**
	 * Whether credentials were provided by the User or if we
	 * need to prompt for them
	 *
	 * @return boolean
	 */
	public function hasCredentials()
	{
		$credentials = $this->getOption('scm');

		return $credentials['username'] or $credentials['password'];
	}

	/**
	 * Get the Git repository
	 *
	 * @param  string $username
	 * @param  string $password
	 *
	 * @return string
	 */
	public function getRepository($username = null, $password = null)
	{
		// Get credentials
		$repository = $this->getOption('scm');
		$username   = $username ?: $repository['username'];
		$password   = $password ?: $repository['password'];
		$repository = $repository['repository'];

		// Add credentials if possible
		if ($username or $password) {

			// Build credentials chain
			$credentials = $username;
			if ($password) $credentials .= ':'.$password;
			$credentials .= '@';

			// Add them in chain
			$repository = Str::contains($repository, 'https://'.$username)
				? str_replace($username.'@', $credentials, $repository)
				: str_replace('https://', 'https://'.$credentials, $repository);
		}

		return $repository;
	}

	/**
	 * Get the Git branch
	 *
	 * @return string
	 */
	public function getRepositoryBranch()
	{
		exec($this->app['rocketeer.scm']->currentBranch(), $fallback);
		$fallback = trim($fallback[0]) ?: 'master';
		$branch   = $this->getOption('scm.branch') ?: $fallback;

		return $branch;
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
		if ($folder and $this->stage) $base .= $this->stage.'/';
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
