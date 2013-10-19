<?php
namespace Rocketeer\Traits;

use Rocketeer\Bash;

/**
 * An abstract Task with common helpers, from which all Tasks derive
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
	 * Execute another Task by name
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
	 * @param string $destination Where to clone to
	 *
	 * @return string
	 */
	public function cloneRepository($destination = null)
	{
		if (!$destination) {
			$destination = $this->releasesManager->getCurrentReleasePath();
		}

		$this->command->info('Cloning repository in "' .$destination. '"');
		$output = $this->scm->execute('checkout', $destination);

		$status = $this->checkStatus('Unable to clone the repository', $output);
		if ($status === false) {
			// Forget the SCM credentials if they're invalid
			$this->server->forgetValue('credentials');
		}

		return $status;
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
		$tasks = array($this->scm->update());

		// Reset if requested
		if ($reset) {
			array_unshift($tasks, $this->scm->reset());
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

		// Get path to current/ folder and latest release
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
		$currentFile = $this->releasesManager->getCurrentReleasePath($file);
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
		$commands = array();

		// Get path to folder
		$folder = $this->releasesManager->getCurrentReleasePath($folder);
		$this->command->comment('Setting permissions for '.$folder);

		// Get permissions options
		$options = $this->rocketeer->getOption('remote.permissions');
		$chmod   = array_get($options, 'permissions');
		$user    = array_get($options, 'apache.user');
		$group   = array_get($options, 'apache.group');

		// Add chmod
		if ($chmod) {
			$commands[] = sprintf('chmod -R %s %s', $chmod, $folder);
			$commands[] = sprintf('chmod -R g+s %s', $folder);
		}

		// And chown
		if ($user and $group) {
			$commands[] = sprintf('chown -R %s:%s %s', $user, $group, $folder);
		}

		// Cancel if setting of permissions is not configured
		if (empty($commands)) {
			return true;
		}

		return $this->runForCurrentRelease($commands);
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

		return $this->checkStatus('Composer could not install dependencies', $output);
	}

	/**
	 * Get the path to Composer binary
	 *
	 * @return string
	 */
	public function getComposer()
	{
		$composer = $this->which('composer', $this->releasesManager->getCurrentReleasePath().'/composer.phar');
		if (strpos($composer, 'composer.phar') !== false) {
			$composer = 'php '.$composer;
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
		if (!$phpunit) {
			return true;
		}

		// Run PHPUnit
		$this->command->info('Running tests...');
		$output = $this->runForCurrentRelease(array(
			$phpunit. ' --stop-on-failure '.$arguments,
		));

		return $this->checkStatus('Tests failed', $output, 'Tests passed successfully');
	}
}
