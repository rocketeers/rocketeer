<?php
/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Rocketeer\Traits\BashModules;

use Illuminate\Support\Str;
use Rocketeer\Abstracts\AbstractLocatorClass;
use Rocketeer\Traits\HasHistory;
use Rocketeer\Traits\HasLocator;

/**
 * Core handling of running commands and returning output
 *
 * @author Maxime Fabre <ehtnam6@gmail.com>
 */
trait Core
{
	use HasLocator;
	use HasHistory;

	////////////////////////////////////////////////////////////////////
	///////////////////////////// CORE METHODS /////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Run actions on the remote server and gather the ouput
	 *
	 * @param  string|array $commands One or more commands
	 * @param  boolean      $silent   Whether the command should stay silent no matter what
	 * @param  boolean      $array    Whether the output should be returned as an array
	 *
	 * @return string|string[]
	 */
	public function run($commands, $silent = false, $array = false)
	{
		$commands = $this->processCommands($commands);
		$verbose  = $this->getOption('verbose') && !$silent;

		// Log the commands
		if (!$silent) {
			$this->toHistory($commands);
		}

		// Display for pretend mode
		if ($this->getOption('pretend') and !$silent) {
			$this->toOutput($commands);
			$this->command->line(implode(PHP_EOL, $commands));

			return sizeof($commands) == 1 ? $commands[0] : $commands;
		}

		// Run commands
		$output = null;
		$this->remote->run($commands, function ($results) use (&$output, $verbose) {
			$output .= $results;

			if ($verbose) {
				$this->remote->display(trim($results));
			}
		});

		// Process and log the output and commands
		$output = $this->processOutput($output, $array, true);
		$this->toOutput($output);

		return $output;
	}

	/**
	 * Run a command get the last line output to
	 * prevent noise
	 *
	 * @param string|array $commands
	 *
	 * @return string
	 */
	public function runLast($commands)
	{
		$results = $this->runRaw($commands, true);
		$results = end($results);

		return $results;
	}

	/**
	 * Run a raw command, without any processing, and
	 * get its output as a string or array
	 *
	 * @param  string|array $commands
	 * @param  boolean      $array Whether the output should be returned as an array
	 * @param  boolean      $trim  Whether the output should be trimmed
	 *
	 * @return string
	 */
	public function runRaw($commands, $array = false, $trim = false)
	{
		// Run commands
		$output = null;
		$this->remote->run($commands, function ($results) use (&$output) {
			$output .= $results;
		});

		// Process the output
		$output = $this->processOutput($output, $array, $trim);

		return $output;
	}

	/**
	 * Run commands silently
	 *
	 * @param string|array $commands
	 * @param boolean      $array
	 *
	 * @return string
	 */
	public function runSilently($commands, $array = false)
	{
		return $this->run($commands, true, $array);
	}

	/**
	 * Run commands in a folder
	 *
	 * @param  string       $folder
	 * @param  string|array $tasks
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
	 * Check the status of the last run command, return an error if any
	 *
	 * @param  string $error   The message to display on error
	 * @param  string $output  The command's output
	 * @param  string $success The message to display on success
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

			return $output || true;
		}

		// Else display the error
		$error = sprintf('An error occured: "%s", while running:'.PHP_EOL.'%s', $error, $output);
		$this->command->error($error);

		return false;
	}

	////////////////////////////////////////////////////////////////////
	/////////////////////////////// HELPERS ////////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Get the current timestamp on the server
	 *
	 * @return string
	 */
	public function getTimestamp()
	{
		$timestamp = $this->runLast('date +"%Y%m%d%H%M%S"');
		$timestamp = trim($timestamp);
		$timestamp = preg_match('/^[0-9]{14}$/', $timestamp) ? $timestamp : date('YmdHis');

		return $timestamp;
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
		return $this->hasCommand() ? $this->command->option($option) : null;
	}

	////////////////////////////////////////////////////////////////////
	///////////////////////////// PROCESSORS ///////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Process an array of commands
	 *
	 * @param  string|array $commands
	 *
	 * @return array
	 */
	public function processCommands($commands)
	{
		$stage     = $this->connections->getStage();
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

			// Add stage flag to Artisan commands
			if (Str::contains($command, 'artisan') and $stage) {
				$command .= ' --env="'.$stage.'"';
			}
		}

		return $commands;
	}

	/**
	 * Process the output of a command
	 *
	 * @param string|array $output
	 * @param boolean      $array Whether to return an array or a string
	 * @param boolean      $trim  Whether to trim the output or not
	 *
	 * @return string|array
	 */
	protected function processOutput($output, $array = false, $trim = true)
	{
		// Remove polluting strings
		$output = str_replace('stdin: is not a tty', null, $output);

		// Explode output if necessary
		if ($array) {
			$output = explode($this->server->getLineEndings(), $output);
		}

		// Trim output
		if ($trim) {
			$output = is_array($output)
				? array_filter($output)
				: trim($output);
		}

		return $output;
	}
}
