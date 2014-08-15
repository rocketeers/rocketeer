<?php
/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Rocketeer;

use Closure;
use Illuminate\Remote\ConnectionInterface;
use Rocketeer\Traits\HasLocator;

/**
 * Stub of local connections to make Rocketeer work
 * locally when necessary
 *
 * @author Maxime Fabre <ehtnam6@gmail.com>
 */
class LocalConnection implements ConnectionInterface
{
	use HasLocator;

	/**
	 * Define a set of commands as a task.
	 *
	 * @param  string       $task
	 * @param  string|array $commands
	 *
	 * @return void
	 */
	public function define($task, $commands)
	{
		// ...
	}

	/**
	 * Run a task against the connection.
	 *
	 * @param  string   $task
	 * @param  \Closure $callback
	 *
	 * @return void
	 */
	public function task($task, Closure $callback = null)
	{
		// ...
	}

	/**
	 * Run a set of commands against the connection.
	 *
	 * @param  string|array $commands
	 * @param  \Closure     $callback
	 *
	 * @return void
	 */
	public function run($commands, Closure $callback = null)
	{
		$commands = (array) $commands;
		foreach ($commands as $command) {
			exec($command, $output);

			if ($callback) {
				$output = implode(PHP_EOL, $output);
				$callback($output);
			}
		}
	}

	/**
	 * Upload a local file to the server.
	 *
	 * @param  string $local
	 * @param  string $remote
	 *
	 * @return void
	 */
	public function put($local, $remote)
	{
		$local = $this->files->get($local);

		return $this->putString($local, $remote);
	}

	/**
	 * Get the contents of a remote file.
	 *
	 * @param  string  $remote
	 * @return string
	 */
	public function getString($remote)
	{
		return $this->files->get($remote);
	}

	/**
	 * Display the given line using the default output.
	 *
	 * @param  string  $line
	 * @return void
	 */
	public function display($line)
	{
		$lead = '<comment>[local]</comment>';

		$this->command->line($lead.' '.$line);
	}

	/**
	 * Upload a string to to the given file on the server.
	 *
	 * @param  string $remote
	 * @param  string $contents
	 *
	 * @return void
	 */
	public function putString($remote, $contents)
	{
		return $this->files->put($remote, $contents);
	}
}
