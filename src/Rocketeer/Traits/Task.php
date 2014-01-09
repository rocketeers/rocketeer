<?php
/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Rocketeer\Traits;

use Rocketeer\Bash;

/**
 * An abstract Task with common helpers, from which all Tasks derive
 *
 * @author Maxime Fabre <ehtnam6@gmail.com>
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

	/**
	 * Fire the command
	 *
	 * @return array
	 */
	public function fire()
	{
		$this->fireEvent('before');
		$results = $this->execute();
		$this->fireEvent('after');

		return $results;
	}

	////////////////////////////////////////////////////////////////////
	/////////////////////////////// EVENTS /////////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Fire an event related to this task
	 *
	 * @param string $event
	 *
	 * @return array|null
	 */
	public function fireEvent($event)
	{
		$event = $this->getQualifiedEvent($event);

		return $this->app['events']->fire($event, array($this));
	}

	/**
	 * Get the fully qualified event name
	 *
	 * @param string $event
	 *
	 * @return string
	 */
	public function getQualifiedEvent($event)
	{
		return 'rocketeer.'.$this->getSlug().'.'.$event;
	}

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
		return (bool) $this->releasesManager->getCurrentRelease();
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
		return $this->app['rocketeer.tasks']->buildTask($task)->fire();
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

		// Executing checkout
		$this->command->info('Cloning repository in "' .$destination. '"');
		$output = $this->scm->execute('checkout', $destination);
		$this->history[] = $output;

		// Cancel if failed and forget credentials
		$success = $this->checkStatus('Unable to clone the repository', $output) !== false;
		if (!$success) {
			$this->server->forgetValue('credentials');

			return false;
		}

		// Deploy submodules
		$this->command->info('Initializing submodules if any');
		$this->runForCurrentRelease($this->scm->submodules());

		return $success;
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
	 * Execute permissions actions on a file with the provided callback
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
		$callback = $this->rocketeer->getOption('remote.permissions.callback');
		$commands = (array) $callback($this, $folder);

		// Cancel if setting of permissions is not configured
		if (empty($commands)) {
			return true;
		}

		return $this->runForCurrentRelease($commands);
	}

	////////////////////////////////////////////////////////////////////
	/////////////////////////// THIRD-PARTY TOOLS //////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Run Composer on the folder
	 *
	 * @return string
	 */
	public function runComposer()
	{
		// Find Composer
		$composer = $this->getComposer();
		if (!$composer) {
			return true;
		}

		// Check for Composer file
		$dependencies = $this->releasesManager->getCurrentReleasePath().'/composer.json';
		if (!$this->fileExists($dependencies)) {
			return true;
		}

		// Run install
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

		// Prepend PHP command
		if (strpos($composer, 'composer.phar') !== false) {
			$composer = $this->php($composer);
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

		return $this->runForCurrentRelease($this->artisan('migrate'.$seed));
	}

	/**
	 * Seed the database
	 *
	 * @param string $class A class to seed
	 *
	 * @return string
	 */
	public function seed($class = null)
	{
		$class = $class ? ' --class="'.$class.'"' : null;

		return $this->runForCurrentRelease($this->artisan('db:seed'.$class));
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
