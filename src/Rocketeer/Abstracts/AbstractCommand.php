<?php
/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Rocketeer\Abstracts;

use Closure;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;

/**
 * An abstract command with various helpers for all
 * subcommands to inherit
 *
 * @author Maxime Fabre <ehtnam6@gmail.com>
 */
abstract class AbstractCommand extends Command
{
	/**
	 * Run the tasks
	 *
	 * @return void
	 */
	abstract public function fire();

	/**
	 * Get the console command options.
	 *
	 * @return string[][]
	 */
	protected function getOptions()
	{
		return array(
			['parallel', 'P', InputOption::VALUE_NONE, 'Run the tasks asynchronously instead of sequentially'],
			['pretend', 'p', InputOption::VALUE_NONE, 'Shows which command would execute without actually doing anything'],
			['on', 'C', InputOption::VALUE_REQUIRED, 'The connection(s) to execute the Task in'],
			['stage', 'S', InputOption::VALUE_REQUIRED, 'The stage to execute the Task in']
		);
	}

	/**
	 * Returns the command name.
	 *
	 * @return string The command name
	 */
	public function getName()
	{
		// Return commands without namespace if standalone
		if (!$this->isInsideLaravel()) {
			return str_replace('deploy:', null, $this->name);
		}

		return $this->name;
	}

	/**
	 * Check if the current command is run in the scope of
	 * Laravel or standalone
	 *
	 * @return boolean
	 */
	public function isInsideLaravel()
	{
		return $this->laravel->bound('artisan');
	}

	////////////////////////////////////////////////////////////////////
	///////////////////////////// CORE METHODS /////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Fire a Tasks Queue
	 *
	 * @param string|string[]|\Rocketeer\Abstracts\AbstractTask[] $tasks
	 *
	 * @return integer
	 */
	protected function fireTasksQueue($tasks)
	{
		// Bind command to container
		$this->laravel->instance('rocketeer.command', $this);

		// Check for credentials
		$this->laravel['rocketeer.credentials']->getServerCredentials();
		$this->laravel['rocketeer.credentials']->getRepositoryCredentials();

		// Convert tasks to array if necessary
		if (!is_array($tasks)) {
			$tasks = array($tasks);
		}

		// Run tasks and display timer
		$status = $this->time(function () use ($tasks) {
			return $this->laravel['rocketeer.tasks']->run($tasks, $this);
		});

		// Remove command instance
		unset($this->laravel['rocketeer.command']);

		return $status ? 0 : 1;
	}

	//////////////////////////////////////////////////////////////////////
	////////////////////////////// HELPERS ///////////////////////////////
	//////////////////////////////////////////////////////////////////////

	/**
	 * Ask a question to the user, with default and/or multiple choices
	 *
	 * @param string      $question
	 * @param string|null $default
	 * @param string[]    $choices
	 *
	 * @return string
	 */
	public function askWith($question, $default = null, $choices = array())
	{
		// If default, show it in the question
		if (!is_null($default)) {
			$question .= ' ('.$default.')';
		}

		// If multiple choices, show them
		if ($choices) {
			$question .= ' ['.implode('/', $choices).']';

			return $this->askWithCompletion($question, $choices, $default);
		}

		return $this->ask($question, $default);
	}

	/**
	 * Time an operation and display it afterwards
	 *
	 * @param Closure $callback
	 *
	 * @return integer;
	 */
	public function time(Closure $callback)
	{
		// Start timer, execute callback, close timer
		$timerStart = microtime(true);
		$results    = $callback();
		$time       = round(microtime(true) - $timerStart, 4);

		$this->line('Execution time: <comment>'.$time.'s</comment>');

		return $results;
	}
}
