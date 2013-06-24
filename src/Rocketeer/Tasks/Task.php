<?php
namespace Rocketeer\Tasks;

use Illuminate\Console\Command;
use Illuminate\Remote\Connection;
use Rocketeer\DeploymentsManager;
use Rocketeer\ReleasesManager;
use Rocketeer\Rocketeer;

/**
 * A Task to execute on the remote servers
 */
abstract class Task
{

	/**
	 * The Releases Manager instance
	 *
	 * @var ReleasesManager
	 */
	public $releasesManager;

	/**
	 * The Deployments Manager instance
	 *
	 * @var DeploymentsManager
	 */
	public $deploymentsManager;

	/**
	 * The Rocketeer instance
	 *
	 * @var Rocketeer
	 */
	public $rocketeer;

	/**
	 * The Remote instance
	 *
	 * @var Connection
	 */
	public $remote;

	/**
	 * The Command instance
	 *
	 * @var Command
	 */
	public $command;

	/**
	 * Build a new Task
	 *
	 * @param Rocketeer       $rocketeer
	 * @param ReleasesManager $releasesManager
	 * @param Connection      $remote
	 * @param Command         $command
	 */
	public function __construct(Rocketeer $rocketeer, ReleasesManager $releasesManager, DeploymentsManager $deploymentsManager, Connection $remote, $command)
	{
		$this->releasesManager    = $releasesManager;
		$this->deploymentsManager = $deploymentsManager;
		$this->rocketeer          = $rocketeer;
		$this->remote             = $remote;
		$this->command            = $command;
	}

	/**
	 * Get the basic name of the Task
	 *
	 * @return string
	 */
	public function getSlug()
	{
		$name = get_class($this);
		$name = str_replace('\\', '/', $name);
		$name = basename($name);

		return strtolower($name);
	}

	/**
	 * Run the Task
	 *
	 * @return  void
	 */
	abstract public function execute();

	////////////////////////////////////////////////////////////////////
	/////////////////////////////// HELPERS ////////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Run actions on the remote server and gather the ouput
	 *
	 * @param  string|array $tasks One or more tasks
	 *
	 * @return string
	 */
	public function run($tasks)
	{
		$output = null;
		$tasks   = (array) $tasks;

		// Run tasks
		$this->remote->run($tasks, function($results) use (&$output) {
			$output = $results;
		});

		// Print output
		print $output;

		return $output;
	}

	/**
	 * Run actions in the current release's folder
	 *
	 * @param  string|array $tasks        One or more tasks
	 *
	 * @return string
	 */
	public function runForCurrentRelease($tasks)
	{
		$tasks = (array) $tasks;
		array_unshift($tasks, 'cd '.$this->releasesManager->getCurrentReleasePath());

		return $this->run($tasks);
	}

	/**
	 * Execute a Task
	 *
	 * @param  string $task
	 *
	 * @return string The Task's output
	 */
	public function executeTask($task)
	{
		$task = new $task($this->rocketeer, $this->releasesManager, $this->deploymentsManager, $this->remote, $this->command);

		return $task->execute();
	}

	////////////////////////////////////////////////////////////////////
	//////////////////////////////// TASKS /////////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Clone the repo into a release folder
	 *
	 * @return string
	 */
	public function cloneRelease()
	{
		$branch      = $this->rocketeer->getGitBranch();
		$repository  = $this->rocketeer->getGitRepository();
		$releasePath = $this->releasesManager->getCurrentReleasePath();

		$this->command->info('Cloning repository in "' .$releasePath. '"');

		return $this->run(sprintf('git clone -b %s %s %s', $branch, $repository, $releasePath));
	}

	/**
	 * Update the current symlink
	 *
	 * @param integer $release A release to mark as current
	 *
	 * @return string
	 */
	public function updateSymlink($release = null)
	{
		// If the release is specified, update to make it the current one
		if ($release) {
			$this->releasesManager->updateCurrentRelease($release);
		}

		$currentReleasePath = $this->releasesManager->getCurrentReleasePath();
		$currentFolder      = $this->rocketeer->getFolder('current');

		return $this->run(sprintf('ln -s %s %s', $currentReleasePath, $currentFolder));
	}

	/**
	 * Set a folder as web-writable
	 *
	 * @param string $folder
	 *
	 * @return  string
	 */
	public function setPermissions($folder)
	{
		$folder = $this->releasesManager->getCurrentReleasePath().'/'.$folder;

		$output  = $this->run(array(
			'chmod -R +x ' .$folder,
			'chown -R www-data:www-data ' .$folder,
		));

		return $output;
	}

	////////////////////////////////////////////////////////////////////
	//////////////////////////////// VENDOR ////////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Run Composer on the folder
	 *
	 * @return string
	 */
	public function runComposer()
	{
		return $this->runForCurrentRelease('composer install');
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
	public function gotoFolder($folder = null)
	{
		return $this->run('cd '.$this->rocketeer->getFolder($folder));
	}

	/**
	 * Create a folder in the application's folder
	 *
	 * @param  string $folder       The folder to create
	 *
	 * @return string The task
	 */
	public function createFolder($folder = null)
	{
		return $this->run('mkdir '.$this->rocketeer->getFolder($folder));
	}

	/**
	 * Remove a folder in the application's folder
	 *
	 * @param  string $folder       The folder to remove
	 *
	 * @return string The task
	 */
	public function removeFolder($folder = null)
	{
		return $this->run('rm -rf '.$this->rocketeer->getFolder($folder));
	}

}
