<?php
namespace Rocketeer\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

abstract class DeployCommand extends Command
{

	// Command attributes -------------------------------------------- /

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'deploy';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Your interface to deploying your projects';

	// Remote attributes --------------------------------------------- /

	/**
	 * The current release ID
	 *
	 * @var integer
	 */
	protected $currentRelease;

	/**
	 * The remote connection
	 *
	 * @var SSH
	 */
	protected $remote;

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct($app)
	{
		parent::__construct();

		$this->laravel        = $app;
		$this->currentRelease = time();
		$this->remote         = $app['remote']->into('production');
	}

	/**
	 * Get the console command arguments.
	 *
	 * @return array
	 */
	protected function getArguments()
	{
		return array(
			array('application', InputArgument::OPTIONAL, 'The name of the application to deploy.'),
		);
	}

	/**
	 * Get the console command options.
	 *
	 * @return array
	 */
	protected function getOptions()
	{
		return array();
	}

	////////////////////////////////////////////////////////////////////
	/////////////////////////////// HELPERS ////////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Get the name of the application to deploy
	 *
	 * @return string
	 */
	protected function getApplicationName()
	{
		return $this->argument('application') ?: $this->laravel['config']->get('rocketeer::remote.application_name');
	}

	/**
	 * Get the Git repository
	 *
	 * @return string
	 */
	protected function getRepository()
	{
		// Get credentials
		$repository = $this->laravel['config']->get('rocketeer::git');
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
	//////////////////////////////// TASKS /////////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Clone the repo into a release folder
	 *
	 * @return string
	 */
	protected function cloneRelease()
	{
		$branch = $this->laravel['config']->get('rocketeer::git.branch');
		$this->info('Cloning repository in "releases/'.$this->currentRelease.'"');

		return 'git clone -b ' .$branch. ' ' .$this->getRepository(). ' ' .$this->getReleasesPath().'/'.$this->currentRelease;
	}

	/**
	 * Run Composer on the folder
	 *
	 * @return string
	 */
	protected function runComposer()
	{
		$this->info('Running Composer');

		return 'composer install';
	}

	/**
	 * Run Bower on the folder
	 *
	 * @return string
	 */
	protected function runBower()
	{
		$this->info('Installing Bower components');

		return 'bower install';
	}

	/**
	 * Run Basset on the folder
	 *
	 * @return string
	 */
	protected function runBasset()
	{
		$this->info('Building Basset collections');

		return 'php artisan basset:build -f --env=production';
	}

	////////////////////////////////////////////////////////////////////
	/////////////////////////////// FOLDERS ////////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Go into a folder
	 *
	 * @param  string $folder
	 *
	 * @return string
	 */
	protected function cd($folder = null)
	{
		return 'cd '.$this->getFolder($folder);
	}

	/**
	 * Remove a folder in the application's folder
	 *
	 * @param  string $folder       The folder to remove
	 *
	 * @return string The task
	 */
	protected function removeFolder($folder = null)
	{
		return 'rm -rf '.$this->getFolder($folder);
	}

	/**
	 * Create a folder in the application's folder
	 *
	 * @param  string $folder       The folder to create
	 *
	 * @return string The task
	 */
	protected function createFolder($folder = null)
	{
		return 'mkdir '.$this->getFolder($folder);
	}

	/**
	 * Update the current symlink
	 *
	 * @return string
	 */
	protected function updateSymlink()
	{
		return 'ln -s '.$this->getCurrentRelease(). ' ' .$this->getFolder('current');
	}

	////////////////////////////////////////////////////////////////////
	//////////////////////////////// PATHS /////////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Get the path to the current release
	 *
	 * @return string
	 */
	protected function getCurrentRelease()
	{
		return $this->getReleasesPath().'/'.$this->currentRelease;
	}

	/**
	 * Get the path to the releases folder
	 *
	 * @return string
	 */
	protected function getReleasesPath()
	{
		return $this->getFolder('releases');
	}

	/**
	 * Get the path to a folder
	 *
	 * @param  strng $folder
	 *
	 * @return string
	 */
	protected function getFolder($folder = null)
	{
		if (!str_contains($folder, $this->getBasePath())) {
			$base = $this->getBasePath();
			if ($folder) $base .= '/'.$folder;
		} else {
			$base = $folder;
		}

		return $base;
	}

	/**
	 * Get the path to the remote folder
	 *
	 * @return string
	 */
	protected function getBasePath()
	{
		$rootDirectory = $this->laravel['config']->get('rocketeer::remote.root_directory');
		$rootDirectory = Str::finish($rootDirectory, '/');

		return $rootDirectory.$this->getApplicationName();
	}

}