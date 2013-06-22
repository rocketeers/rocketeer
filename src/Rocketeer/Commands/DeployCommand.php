<?php
namespace Rocketeer\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

abstract class DeployCommand extends Command
{

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
	public function __construct()
	{
		parent::__construct();

		$this->currentRelease = time();
		$this->remote = SSH::into('production');
	}

	/**
	 * Define the tasks
	 */
	protected function defineTasks()
	{
		// Create Tasks
		$this->remote->define('cloneRelease', array(
			$this->cloneRelease(),
			$this->removeFolder('current'),
			$this->updateSymlink(),
		));

		$this->remote->define('setupRelease', array(
			$this->cd($this->getCurrentRelease()),
			$this->runComposer(),
			$this->runBower(),
			$this->runBasset(),
	    "chmod -R +x " .$this->getCurrentRelease().'/app',
	    "chmod -R +x " .$this->getCurrentRelease().'/public',
	    "chown -R www-data:www-data " .$this->getCurrentRelease().'/app',
	    "chown -R www-data:www-data " .$this->getCurrentRelease().'/public',
		));

		$this->remote->define('setupFolders', array(
			$this->removeFolder(),
			$this->createFolder(),
			$this->createFolder('releases'),
			$this->createFolder('current'),
		));
	}

	/**
	 * Get the console command arguments.
	 *
	 * @return array
	 */
	protected function getArguments()
	{
		return array(
			array('website', InputArgument::REQUIRED, 'The website to deploy.'),
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
	 * Set the correct permissions on the current release folder
	 */
	protected function setPermissions()
	{
    return array(
    );
	}

	/**
	 * Get the Git repository
	 *
	 * @return string
	 */
	protected function getRepository()
	{
		return Config::get('remote.git.repository');
	}

	/**
	 * Clone the repo into a release folder
	 *
	 * @return string
	 */
	protected function cloneRelease()
	{
		return 'git clone ' .$this->getRepository(). ' ' .$this->getReleasesPath().'/'.$this->currentRelease;
	}

	/**
	 * Run Composer on the folder
	 *
	 * @return string
	 */
	protected function runComposer()
	{
		return 'composer install';
	}

	/**
	 * Run Bower on the folder
	 *
	 * @return string
	 */
	protected function runBower()
	{
		return 'bower install';
	}

	/**
	 * Run Basset on the folder
	 *
	 * @return string
	 */
	protected function runBasset()
	{
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

	/**
	 * Clean up old releases
	 *
	 * @return string
	 */
	protected function cleanReleases()
	{
		return null;
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
		if (!str_contains($folder, $this->getPath())) {
			$base = $this->getPath();
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
	protected function getPath()
	{
		return '/home/www/'.$this->argument('website');
	}

}