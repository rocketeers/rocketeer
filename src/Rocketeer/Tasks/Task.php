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
	public function __construct(Rocketeer $rocketeer, ReleasesManager $releasesManager, DeploymentsManager $deploymentsManager, Connection $remote, Command $command)
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
	public function getName()
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
	 * @param  string $task
	 *
	 * @return string
	 */
	public function run($task)
	{
		$output = null;
		$this->remote->run(array($task), function($results) use (&$output) {
			$output = $results;
		});

		return $output;
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
	protected function updateSymlink($release = null)
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
	protected function setPermissions($folder)
	{
		$folder = $this->releasesManager->getCurrentReleasePath().'/'.$folder;

		$output  = $this->run('chmod -R +x ' .$folder);
		$output .= $this->run('chown -R www-data:www-data ' .$folder);

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
	protected function runComposer()
	{
		return $this->run('composer install');
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
		return $this->run('cd '.$this->rocketeer->getFolder($folder));
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
		return $this->run('mkdir '.$this->rocketeer->getFolder($folder));
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
		return $this->run('rm -rf '.$this->rocketeer->getFolder($folder));
	}

}