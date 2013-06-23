<?php
namespace Rocketeer\Commands;

use Illuminate\Console\Command;

/**
 * A basic deploy command with helpers
 */
abstract class BaseDeployCommand extends Command
{

	/**
	 * The remote connection
	 *
	 * @var SSH
	 */
	protected $remote;

	/**
	 * Create a new Command instance
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Run the tasks
	 *
	 * @return void
	 */
	public function fire()
	{
		$this->getRemote()->run(
			$this->getTasks()
		);
	}

	/**
	 * Fire a Tasks Queue
	 *
	 * @param  array $tasks
	 *
	 * @return [type] [description]
	 */
	protected function fireTasksQueue($tasks)
	{
		return $this->laravel['rocketeer.tasks']->run($tasks, $this);
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
	 * Get the Remote connection
	 *
	 * @return Connection
	 */
	protected function getRemote()
	{
		// Setup remote connection
		if (!$this->remote) {
			$connections  = $this->laravel['config']->get('rocketeer::connections');
			$this->remote = $this->laravel['remote']->into($connections);
		}

		return $this->remote;
	}

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

	/**
	 * Get the DeploymentsManager instance
	 *
	 * @return DeploymentsManager
	 */
	protected function getDeploymentsManager()
	{
		return $this->laravel['rocketeer.deployments'];
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
	 * Update the current symlink
	 *
	 * @param integer $release A release to mark as current
	 *
	 * @return string
	 */
	protected function updateSymlink($release = null)
	{
		// If the release is specified, update to make it the current one
		if ($release) {
			$this->getReleasesManager()->updateCurrentRelease($release);
		}

		$currentReleasePath = $this->getReleasesManager()->getCurrentReleasePath();
		$currentFolder      = $this->getRocketeer()->getFolder('current');

		return sprintf('ln -s %s %s', $currentReleasePath, $currentFolder);
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
	protected function tasks()
	{
		return array();
	}

	/**
	 * Get the tasks to execute
	 *
	 * @return array
	 */
	public function getTasks()
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
