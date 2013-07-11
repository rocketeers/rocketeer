<?php
namespace Rocketeer;

use Illuminate\Console\Command;
use Illuminate\Container\Container;
use Illuminate\Remote\Connection;
use Illuminate\Support\Str;

/**
 * An helper to execute commands on the remote server
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
	 * The Releases Manager instance
	 *
	 * @var ReleasesManager
	 */
	public $releasesManager;

	/**
	 * The Server instance
	 *
	 * @var Server
	 */
	public $server;

	/**
	 * The SCM
	 *
	 * @var Scm
	 */
	public $scm;

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
	 * @param Command|null $command
	 */
	public function __construct(Container $app, $command = null)
	{
		$this->app             = $app;
		$this->releasesManager = $app['rocketeer.releases'];
		$this->server          = $app['rocketeer.server'];
		$this->rocketeer       = $app['rocketeer.rocketeer'];
		$this->scm             = $app['rocketeer.scm'];
		$this->remote          = $app['remote'];
		$this->command         = $command;
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
		$commands = $this->processCommands($commands);

		// Log the commands for pretend
		if ($this->getOption('pretend') and !$silent) {
			$this->command->line(implode(PHP_EOL, $commands));
			$commands = (sizeof($commands) == 1) ? $commands[0] : $commands;

			return $commands;
		}

		// Get output
		$output = $this->runRemoteCommands($commands, $array);
		$output = is_array($output) ? array_filter($output) : trim($output);

		// Print if necessary
		if ($this->getOption('verbose') and !$silent) {
			print is_array($output) ? implode(PHP_EOL, $output) : $output;
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
	 * Run a raw command, without any processing, and
	 * get its output as a string or array
	 *
	 * @param  string|array $commands
	 * @param  boolean      $array     Whether the output should be returned as an array
	 *
	 * @return string
	 */
	public function runRemoteCommands($commands, $array = false)
	{
		$output = null;

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
			if (DIRECTORY_SEPARATOR !== $separator) {
				$command = str_replace(DIRECTORY_SEPARATOR, $separator, $command);
			}

			// Add stage flag
			if (Str::startsWith($command, 'php artisan') and $stage) {
				$command .= ' --env='.$stage;
			}

		}

		return $commands;
	}
}
