<?php
namespace Rocketeer\Tasks;

use Illuminate\Console\Command;
use Illuminate\Container\Container;
use Illuminate\Remote\Connection;
use Rocketeer\DeploymentsManager;
use Rocketeer\ReleasesManager;
use Rocketeer\Rocketeer;
use Rocketeer\TasksQueue;

/**
 * A Task to execute on the remote servers
 */
abstract class Task
{

	/**
	 * The IoC Container
	 *
	 * @var Container
	 */
	protected $app;

	/**
	 * The TasksQueue instance
	 *
	 * @var TasksQueue
	 */
	protected $tasksQueue;

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
	 * A description of what the Task does
	 *
	 * @var string
	 */
	public $description;

	/**
	 * Build a new Task
	 *
	 * @param Container    $app
	 * @param TasksQueue   $tasksQueue
	 * @param Command|null $command
	 */
	public function __construct(Container $app, TasksQueue $tasksQueue, $command)
	{
		$this->app                = $app;
		$this->releasesManager    = $app['rocketeer.releases'];
		$this->deploymentsManager = $app['rocketeer.deployments'];
		$this->rocketeer          = $app['rocketeer.rocketeer'];
		$this->remote             = $app['remote'];
		$this->tasksQueue         = $tasksQueue;
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
	 * Run actions on the remote server and gather the ouput
	 *
	 * @param  string|array $tasks  One or more tasks
	 * @param  boolean      $silent Whether the command should stay silent no matter what
	 *
	 * @return string
	 */
	public function run($tasks, $silent = false)
	{
		$output = null;
		$tasks  = (array) $tasks;

		// Log the commands for pretend
		if ($this->command->option('pretend') and !$silent) {
			return $this->command->line(implode(PHP_EOL, $tasks));
		}

		// Run tasks
		$this->remote->run($tasks, function($results) use (&$output) {
			$output .= $results;
		});

		// Print output
		$output = trim($output);
		if ($this->command->option('verbose')) {
			print $output;
		}

		return $output;
	}

	/**
	 * Run actions in a folder
	 *
	 * @param  string        $folder
	 * @param  string|array  $tasks
	 *
	 * @return string
	 */
	public function runInFolder($folder = null, $tasks = array())
	{
		$tasks = (array) $tasks;
		array_unshift($tasks, 'cd '.$this->rocketeer->getFolder($folder));

		return $this->run($tasks);
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
		return $this->tasksQueue->buildTask($task)->execute();
	}

	/**
	 * Get a binary
	 *
	 * @param  string $binary       The name of the binary
	 * @param  string $fallback     A fallback location
	 *
	 * @return string
	 */
	public function which($binary, $fallback = null)
	{
		$location = $this->run('which '.$binary);
		if (!$location or $location == $binary. ' not found') {
			if (!is_null($fallback) and $this->run('which ' .$fallback) != $fallback. ' not found') {
				return $fallback;
			}

			return false;
		}

		return $location;
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

		return $this->runForCurrentRelease($this->getComposer(). ' install');
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

		$testsSucceeded = str_contains($output, 'OK') or str_contains($output, 'No tests executed');
		if ($testsSucceeded) {
			$this->command->info('Tests ran with success');
		} else {
			print $output;
		}

		return $testsSucceeded;
	}

	////////////////////////////////////////////////////////////////////
	/////////////////////////////// FOLDERS ////////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Symlinks two folders
	 *
	 * @param  string $folder   The original folder
	 * @param  string $symlink  The folder that will symlink to it
	 *
	 * @return string
	 */
	public function symlink($folder, $symlink)
	{
		// Remove existing folder or file if existing
		$this->run('rm -rf '.$symlink);

		return $this->run(sprintf('ln -s %s %s', $folder, $symlink));
	}

	/**
	 * Move a file
	 *
	 * @param  string $from
	 * @param  string $to
	 *
	 * @return string
	 */
	public function move($from, $to)
	{
		$folder = dirname($to);
		if (!$this->fileExists($folder)) {
			$this->createFolder($folder, true);
		}

		return $this->run(sprintf('mv %s %s', $from, $to));
	}

	/**
	 * Get the contents of a directory
	 *
	 * @param  string $directory
	 *
	 * @return array
	 */
	public function listContents($directory)
	{
		$contents = $this->run(array('cd '.$directory, 'ls'));
		$contents = explode(PHP_EOL, $contents);

		return $contents;
	}

	/**
	 * Check if a file exists
	 *
	 * @param  string $file Path to the file
	 *
	 * @return boolean
	 */
	public function fileExists($file)
	{
		$exists = $this->run('if [ -e ' .$file. ' ]; then echo "true"; fi', true);

		return $exists == 'true';
	}

	/**
	 * Create a folder in the application's folder
	 *
	 * @param  string  $folder       The folder to create
	 * @param  boolean $recursive
	 *
	 * @return string The task
	 */
	public function createFolder($folder = null, $recursive = false)
	{
		$recursive = $recursive ? '-p ' : null;

		return $this->run('mkdir '.$recursive.$this->rocketeer->getFolder($folder));
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
