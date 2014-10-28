<?php
namespace Rocketeer\Services\Ignition;

use Rocketeer\Abstracts\AbstractTask;
use Rocketeer\Tasks\Closure;
use Rocketeer\Traits\HasLocator;

class Tasks
{
	use HasLocator;

	/**
	 * Get an array of all already defined tasks
	 *
	 * @return array
	 */
	public function getPredefinedTasks()
	{
		$tasks = array(
			''               => 'Rocketeer',
			'check'          => 'Check',
			'cleanup'        => 'Cleanup',
			'current'        => 'CurrentRelease',
			'deploy'         => 'Deploy',
			'flush'          => 'Flush',
			'ignite'         => 'Ignite',
			'rollback'       => 'Rollback',
			'setup'          => 'Setup',
			'strategies'     => 'Strategies',
			'teardown'       => 'Teardown',
			'test'           => 'Test',
			'update'         => 'Update',
			'plugin-publish' => 'Plugins\Publish',
			'plugin-list'    => 'Plugins\List',
			'plugin-install' => 'Plugins\Install',
		);

		// Add user commands
		$userTasks = (array) $this->config->get('rocketeer::hooks.custom');
		$tasks     = array_merge($tasks, $userTasks);

		return $tasks;
	}

	/**
	 * Register an array of tasks and their commands
	 *
	 * @param array $tasks
	 *
	 * @return array The registered commands
	 */
	public function registerTasksAndCommands(array $tasks)
	{
		$commands = [];

		foreach ($tasks as $slug => $task) {

			// Build the related command
			$command = $this->builder->buildCommand($task, $slug);
			$task    = $command->getTask();

			// Bind task to container
			$slug   = $this->getTaskHandle($slug, $task);
			$handle = 'rocketeer.tasks.'.$slug;
			$this->app->bind($handle, function () use ($task) {
				return $task;
			});

			// Remember handle of the command
			$commandHandle = trim('rocketeer.commands.'.$slug, '.');
			$commands[]    = $commandHandle;

			// Register command with the container
			$this->app->singleton($commandHandle, function () use ($command) {
				return $command;
			});
		}

		return $commands;
	}

	/**
	 * Get the handle matching a task
	 *
	 * @param string       $slug
	 * @param AbstractTask $task
	 *
	 * @return string|null
	 */
	public function getTaskHandle($slug, AbstractTask $task = null)
	{
		$slug = ($slug || !$task) ? $slug : $task->getSlug();
		if ($slug == 'closure') {
			return;
		}

		return $slug;
	}
}
