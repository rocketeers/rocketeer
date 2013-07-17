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
	const VERSION = '0.6.4';

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
	 * Get an option from Rocketeer's config file
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
	 * Set the stage Tasks will execute on
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
	 * Get the various stages provided by the User
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
	 * Whether the repository used is using SSH or HTTPS
	 *
	 * @return boolean
	 */
	public function needsCredentials()
	{
		$repository = array_get($this->getCredentials(), 'repository');

		return Str::contains($repository, 'https://');
	}

	/**
	 * Get the available connections
	 *
	 * @return array
	 */
	public function getConnections()
	{
		$connections = $this->app['rocketeer.server']->getValue('connections');
		if (!$connections) {
			$connections = $this->app['config']->get('remote.connections');
		}

		return $connections;
	}

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
	 * Get the credentials for the repository
	 *
	 * @return array
	 */
	public function getCredentials()
	{
		$credentials = $this->app['rocketeer.server']->getValue('credentials');
		if (!$credentials) {
			$credentials = $this->getOption('scm');
		}

		return $credentials;
	}

	/**
	 * Get the URL to the Git repository
	 *
	 * @param  string $username
	 * @param  string $password
	 *
	 * @return string
	 */
	public function getRepository()
	{
		// Get credentials
		$repository = $this->getCredentials();
		$username   = array_get($repository, 'username');
		$password   = array_get($repository, 'password');
		$repository = array_get($repository, 'repository');

		// Add credentials if possible
		if ($username or $password) {

			// Build credentials chain
			$credentials  = $password ? $username.':'.$password : $username;
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
	 * Get the path to a folder, taking into account application name and stage
	 *
	 * @param  string $folder
	 *
	 * @return string
	 */
	public function getFolder($folder = null)
	{
		$base = $this->getHomeFolder().'/';
		if ($folder and $this->stage) {
			$base .= $this->stage.'/';
		}
		$folder = str_replace($base, null, $folder);

		return $base.$folder;
	}

	/**
	 * Get the path to the root folder of the application
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
