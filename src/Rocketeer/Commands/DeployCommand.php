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

		$this->laravel = $app;
		$this->remote  = $app['remote']->into('production');
	}

	/**
	 * Run the tasks
	 *
	 * @return void
	 */
	public function fire()
	{
		$this->remote->run($this->getTasks());
	}

	/**
	 * Get the console command arguments.
	 *
	 * @return array
	 */
	protected function getArguments()
	{
		return array();
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
	 * Get the Rocketeer instance
	 *
	 * @return Rocketeer
	 */
	protected function getRocketeer()
	{
		return $this->laravel['rocketeer.rocketeer'];
	}

	/**
	 * Get the ReleasesManager instance
	 *
	 * @return ReleasesManager
	 */
	protected function getReleasesManager()
	{
		return $this->laravel['rocketeer.releases'];
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
		$branch      = $this->laravel['config']->get('rocketeer::git.branch');
		$repository  = $this->getRocketeer()->getGitRepository();
		$releasePath = $this->getReleasesManager()->getCurrentReleasePath();

		$this->info('Cloning repository in "' .$releasePath. '"');

		return sprintf('git clone -b %s %s %s', $branch, $repository, $releasePath);
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
	protected function gotoFolder($folder = null)
	{
		return 'cd '.$this->getRocketeer()->getFolder($folder);
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
		return 'rm -rf '.$this->getRocketeer()->getFolder($folder);
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
		return 'mkdir '.$this->getRocketeer()->getFolder($folder);
	}

	////////////////////////////////////////////////////////////////////
	//////////////////////////////// TASKS /////////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Define here the tasks the command should execute
	 *
	 * @return array
	 */
	abstract protected function tasks();

	/**
	 * Get the tasks to execute
	 *
	 * @return array
	 */
	protected function getTasks()
	{
		return array_merge($this->getBeforeTasks(), $this->tasks(), $this->getAfterTasks());
	}

	/**
	 * Return the tasks the User defined to be executed before this command
	 *
	 * @return array
	 */
	protected function getBeforeTasks()
	{
		return (array) $this->laravel['config']->get('rocketeer::tasks.before.'.$this->name);
	}

	/**
	 * Return the tasks the User defined to be executed after this command
	 *
	 * @return array
	 */
	protected function getAfterTasks()
	{
		return (array) $this->laravel['config']->get('rocketeer::tasks.after.'.$this->name);
	}

}