<?php
namespace Rocketeer;

use Illuminate\Console\Command;
use Illuminate\Container\Container;
use Illuminate\Support\Str;

/**
 * An helper to execute low-level commands on the remote server
 *
 * @property ReleasesManager              $releasesManager
 * @property Rocketeer                    $rocketeer
 * @property Server                       $server
 * @property Illuminate\Remote\Connection $remote
 * @property Traits\Scm                   $scm
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
	 * The Command instance
	 *
	 * @var Command
	 */
	public $command;

	/**
	 * An history of executed commands
	 *
	 * @var array
	 */
	protected $history;

	/**
	 * Build a new Task
	 *
	 * @param Container    $app
	 * @param Command|null $command
	 */
	public function __construct(Container $app, $command = null)
	{
		$this->app     = $app;
		$this->command = $command;
	}

	/**
	 * Get an instance from the Container
	 *
	 * @param  string $key
	 *
	 * @return object
	 */
	public function __get($key)
	{
		$shortcuts = array(
			'releasesManager' => 'rocketeer.releases',
			'server'          => 'rocketeer.server',
			'rocketeer'       => 'rocketeer.rocketeer',
			'scm'             => 'rocketeer.scm',
		);

		// Replace shortcuts
		if (array_key_exists($key, $shortcuts)) {
			$key = $shortcuts[$key];
		}

		return $this->app[$key];
	}

	/**
	 * Set an instance on the Container
	 *
	 * @param string $key
	 * @param object $value
	 */
	public function __set($key, $value)
	{
		$this->app[$key] = $value;
	}

	////////////////////////////////////////////////////////////////////
	///////////////////////////// CORE METHODS /////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Run actions on the remote server and gather the ouput
	 *
	 * @param  string|array $commands  One or more commands
	 * @param  boolean      $silent    Whether the command should stay silent no matter what
	 * @param  boolean      $array     Whether the output should be returned as an array
	 *
	 * @return string|array
	 */
	public function run($commands, $silent = false, $array = false)
	{
		$output   = null;
		$commands = $this->processCommands($commands);
		$verbose  = $this->getOption('verbose') and !$silent;

		// Log the commands for pretend
		if ($this->getOption('pretend') and !$silent) {
			$this->command->line(implode(PHP_EOL, $commands));
			$commands = (sizeof($commands) == 1) ? $commands[0] : $commands;
			$this->history[] = $commands;

			return $commands;
		}

		// Run commands
		$bash = $this;
		$this->remote->run($commands, function ($results) use (&$output, $verbose, $bash) {
			$output .= $results;

			if ($verbose) {
				$bash->remote->display(trim($results));
			}
		});

		// Explode output if necessary
		if ($array) {
			$output = explode($this->server->getLineEndings(), $output);
		}

		// Trim output
		$output = is_array($output)
			? array_filter($output)
			: trim($output);

		// Append output
		$this->history[] = $output;

		return $output;
	}

	/**
	 * Run a raw command, without any processing, and
	 * get its output as a string or array
	 *
	 * @param  string|array $commands
	 * @param  boolean      $array     Whether the output should be returned as an array
	 *
	 * @return string
	 */
	public function runRaw($commands, $array = false)
	{
		$output  = null;

		// Run commands
		$this->remote->run($commands, function ($results) use (&$output) {
			$output .= $results;
		});

		// Explode output if necessary
		if ($array) {
			$output = explode($this->server->getLineEndings(), $output);
			$output = array_filter($output);
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
		// Convert to array
		if (!is_array($tasks)) {
			$tasks = array($tasks);
		}

		// Prepend folder
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
		// Get custom path if any was set
		$custom = 'paths.'.$binary;
		if ($location = $this->server->getValue($custom)) {
			return $location;
		}

		// Else ask the server where the binary is
		$location = $this->run('which '.$binary, true);
		if ($location and $this->fileExists($location)) {
			return $location;
		}

		// Else use the fallback path
		if ($fallback) {
			$location = $this->run('which '.$fallback, true);
			if ($location and $this->fileExists($location)) {
				return $location;
			}
		}

		// Else prompt the User for the actual path
		$location = $this->command->ask($binary. ' could not be found, please enter the path to it');
		if ($location) {
			$this->server->setValue($custom, $location);
			return $location;
		}

		return false;
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
	public function checkStatus($error, $output = null, $success = null)
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
	 * @param  string $folder   The folder in shared/
	 * @param  string $symlink  The folder that will symlink to it
	 *
	 * @return string
	 */
	public function symlink($folder, $symlink)
	{
		if (!$this->fileExists($folder)) {
			if (!$this->fileExists($symlink)) {
				return false;
			}

			$this->move($symlink, $folder);
		}

		// Remove existing symlink
		$this->removeFolder($symlink);

		return $this->run(sprintf('ln -s %s %s', $folder, $symlink));
	}

	/**
	 * Move a file
	 *
	 * @param  string $origin
	 * @param  string $destination
	 *
	 * @return string
	 */
	public function move($origin, $destination)
	{
		$folder = dirname($destination);
		if (!$this->fileExists($folder)) {
			$this->createFolder($folder, true);
		}

		return $this->run(sprintf('mv %s %s', $origin, $destination));
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
		return $this->run('ls '.$directory, false, true);
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
		$exists = $this->runRaw('if [ -e ' .$file. ' ]; then echo "true"; fi');

		return trim($exists) == 'true';
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
	 * Get an option from the Command
	 *
	 * @param  string $option
	 *
	 * @return string
	 */
	protected function getOption($option)
	{
		return $this->command ? $this->command->option($option) : null;
	}

	/**
	 * Process an array of commands
	 *
	 * @param  string|array  $commands
	 *
	 * @return array
	 */
	protected function processCommands($commands)
	{
		$stage     = $this->rocketeer->getStage();
		$separator = $this->server->getSeparator();

		// Cast commands to array
		if (!is_array($commands)) {
			$commands = array($commands);
		}

		// Process commands
		foreach ($commands as &$command) {

			// Replace directory separators
			if (DS !== $separator) {
				$command = str_replace(DS, $separator, $command);
			}

			// Add stage flag
			if (Str::startsWith($command, 'php artisan') and $stage) {
				$command .= ' --env='.$stage;
			}

		}

		return $commands;
	}
}
