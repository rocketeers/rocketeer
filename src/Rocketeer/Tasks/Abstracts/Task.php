<?php
namespace Rocketeer\Tasks\Abstracts;

use Rocketeer\Bash;

/**
 * A Task to execute on the remote servers
 */
abstract class Task extends Bash
{

	/**
	 * A description of what the Task does
	 *
	 * @var string
	 */
	protected $description;

	/**
	 * Whether the Task needs to be run on each stage or globally
	 *
	 * @var boolean
	 */
	public $usesStages = true;

	////////////////////////////////////////////////////////////////////
	///////////////////////////// CORE METHODS /////////////////////////
	////////////////////////////////////////////////////////////////////

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
	 * Get what the Task does
	 *
	 * @return string
	 */
	public function getDescription()
	{
		return $this->description;
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
	 * Check if the remote server is setup
	 *
	 * @return boolean
	 */
	public function isSetup()
	{
		return $this->fileExists($this->rocketeer->getFolder('current'));
	}

	/**
	 * Check if the Task uses stages
	 *
	 * @return boolean
	 */
	public function usesStages()
	{
		$stages = $this->rocketeer->getStages();

		return $this->usesStages and !empty($stages);
	}

	/**
	 * Run actions in the current release's folder
	 *
	 * @param  string|array $tasks One or more tasks
	 *
	 * @return string
	 */
	public function runForCurrentRelease($tasks)
	{
		return $this->runInFolder($this->releasesManager->getCurrentReleasePath(), $tasks);
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
		return $this->app['rocketeer.tasks']->buildTask($task)->execute();
	}

	////////////////////////////////////////////////////////////////////
	//////////////////////////////// TASKS /////////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Clone the repo into a release folder
	 *
	 * @param string $repository The repository to clone
	 * @param string $branch     The branch to clone
	 *
	 * @return string
	 */
	public function cloneRepository($repository, $branch = 'master')
	{
		$releasePath = $this->releasesManager->getCurrentReleasePath();

		$this->command->info('Cloning repository in "' .$releasePath. '"');
		$output = $this->run(sprintf('git clone -b %s %s %s', $branch, $repository, $releasePath));

		return $this->checkStatus('Unable to clone the repository', $output);
	}

	/**
	 * Update the current release
	 *
	 * @param boolean $reset Whether the repository should be reset first
	 *
	 * @return string
	 */
	public function updateRepository($reset = true)
	{
		$this->command->info('Pulling changes');
		$tasks = array('git pull');

		// Reset if requested
		if ($reset) {
			array_unshift($tasks, 'git reset --hard');
		}

		return $this->runForCurrentRelease($tasks);
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

		return $this->symlink($currentReleasePath, $currentFolder);
	}

	/**
	 * Share a file or folder between releases
	 *
	 * @param  string $file Path to the file in a release folder
	 *
	 * @return string
	 */
	public function share($file)
	{
		// Get path to current file and shared file
		$currentFile = $file;
		$sharedFile  = preg_replace('#releases/[0-9]+/#', 'shared/', $currentFile);

		// If no instance of the shared file exists, use current one
		if (!$this->fileExists($sharedFile)) {
			$this->move($currentFile, $sharedFile);
		}

		$this->command->comment('Sharing file '.$currentFile);

		return $this->symlink($sharedFile, $currentFile);
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
		$this->command->comment('Setting permissions for '.$folder);

		$output  = $this->run(array(
			'chmod -R +x ' .$folder,
			'chmod -R g+s ' .$folder,
			'chown -R www-data:www-data ' .$folder,
		));

		return $output;
	}

	////////////////////////////////////////////////////////////////////
	//////////////////////// LARAVEL-SPECIFIC TASKS ////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Run Composer on the folder
	 *
	 * @return string
	 */
	public function runComposer()
	{
		$this->command->comment('Installing Composer dependencies');
		$output = $this->runForCurrentRelease($this->getComposer(). ' install');
		$status = $this->remote->status();

		return $this->checkStatus('Composer could not install dependencies', $output);
	}

	/**
	 * Get the Composer binary
	 *
	 * @return string
	 */
	public function getComposer()
	{
		$composer = $this->which('composer');
		if (!$composer and file_exists($this->app['path.base'].'/composer.phar')) {
			$composer = 'composer.phar';
		}

		return $composer;
	}

	/**
	 * Run any outstanding migrations
	 *
	 * @param boolean $seed Whether the database should also be seeded
	 *
	 * @return string
	 */
	public function runMigrations($seed = false)
	{
		$seed = $seed ? ' --seed' : null;
		$this->command->comment('Running outstanding migrations');

		return $this->runForCurrentRelease('php artisan migrate'.$seed);
	}

	/**
	 * Run the application's tests
	 *
	 * @param string $arguments Additional arguments to pass to PHPUnit
	 *
	 * @return boolean
	 */
	public function runTests($arguments = null)
	{
		// Look for PHPUnit
		$phpunit = $this->which('phpunit', $this->releasesManager->getCurrentReleasePath().'/vendor/bin/phpunit');
		if (!$phpunit) return true;

		// Run PHPUnit
		$this->command->info('Running tests...');
		$output = $this->runForCurrentRelease(array(
			$phpunit. ' --stop-on-failure '.$arguments,
		));

		return $this->checkStatus('Tests failed', $output, 'Tests passed successfully');
	}

}
