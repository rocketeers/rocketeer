<?php
namespace Rocketeer\Tasks\Abstracts;

use Illuminate\Console\Command;
use Illuminate\Container\Container;
use Illuminate\Remote\Connection;
use Illuminate\Support\Str;
use Rocketeer\Rocketeer;
use Rocketeer\TasksQueue;

/**
 * A bash Bash helper to execute commands
 * on the remote server
 */
class Bash
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

	////////////////////////////////////////////////////////////////////
	///////////////////////////// CORE METHODS /////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Run actions on the remote server and gather the ouput
	 *
	 * @param  string|array $commands  One or more commands
	 * @param  boolean      $silent    Whether the command should stay silent no matter what
	 *
	 * @return string
	 */
	public function run($commands, $silent = false)
	{
		$output   = null;
		$commands = $this->processCommands($commands);

		// Log the commands for pretend
		if ($this->command->option('pretend') and !$silent) {
			$this->command->line(implode(PHP_EOL, $commands));
			return true;
		}

		// Run commands
		$this->remote->run($commands, function($results) use (&$output) {
			$output .= $results;
		});

		// Print output
		$output = trim($output);
		if ($this->command->option('verbose') and !$silent) {
			print $output;
		}

		return $output;
	}

	/**
	 * Run commands in a folder
	 *
	 * @param  string        $folder
	 * @param  string|array  $tasks
	 *
	 * @return string
	 */
	public function runInFolder($folder = null, $tasks = array())
	{
		if (!is_array($tasks)) $tasks = array($tasks);
		array_unshift($tasks, 'cd '.$this->rocketeer->getFolder($folder));

		return $this->run($tasks);
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
		$location = $this->run('which '.$binary, true);
		if (!$location or $location == $binary. ' not found') {
			if (!is_null($fallback) and $this->run('which ' .$fallback, true) != $fallback. ' not found') {
				return $fallback;
			}

			return false;
		}

		return $location;
	}

	/**
	 * Check the status of the last run command, return an error if any
	 *
	 * @param  string $error        The message to display on error
	 * @param  string $output       The command's output
	 * @param  string $success      The message to display on success
	 *
	 * @return boolean|string
	 */
	protected function checkStatus($error, $output = null, $success = null)
	{
		// If all went well
		if ($this->remote->status() == 0) {
			if ($success) {
				$this->command->comment($success);
			}

			return $output;
		}

		// Else
		$this->command->error($error);
		print $output.PHP_EOL;

		return false;
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

	////////////////////////////////////////////////////////////////////
	/////////////////////////////// HELPERS ////////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Process an array of commands
	 *
	 * @param  string|array  $commands
	 *
	 * @return array
	 */
	protected function processCommands($commands)
	{
		// Get stage and cast commands to array
		$stage = $this->rocketeer->getStage();
		if (!is_array($commands)) {
			$commands = array($commands);
		}

		// Process commands
		foreach ($commands as &$command) {
			if (Str::startsWith($command, 'php artisan') and $stage) {
				$command .= ' --env='.$stage;
			}
		}

		return $commands;
	}

}
