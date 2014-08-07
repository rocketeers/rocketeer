<?php
/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Rocketeer\Plugins;

use Rocketeer\Abstracts\Plugin;
use Rocketeer\Abstracts\Task;
use Rocketeer\Services\TasksHandler;

/**
 * A base class for notification services to extends
 */
abstract class Notifier extends Plugin
{
	/**
	 * Register Tasks with Rocketeer
	 *
	 * @param \Rocketeer\Services\TasksHandler $queue
	 *
	 * @return void
	 */
	public function onQueue(TasksHandler $queue)
	{
		$me = $this;

		$queue->before('deploy', function ($task) use ($me) {
			$me->prepareThenSend($task, 'before_deploy');
		}, -10);

		$queue->after('deploy', function ($task) use ($me) {
			$me->prepareThenSend($task, 'after_deploy');
		}, -10);
	}

	/**
	 * Send a given message
	 *
	 * @param string $message
	 *
	 * @return void
	 */
	abstract public function send($message);

	////////////////////////////////////////////////////////////////////
	/////////////////////////////// MESSAGE ////////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Get the message's components
	 *
	 * @return string[]
	 */
	protected function getComponents()
	{
		// Get user name
		$user = $this->localStorage->get('notifier.name');
		if (!$user) {
			$user = $this->command->ask('Who is deploying ?');
			$this->localStorage->set('notifier.name', $user);
		}

		// Get what was deployed
		$branch     = $this->connections->getRepositoryBranch();
		$stage      = $this->connections->getStage();
		$connection = $this->connections->getConnection();
		$server     = $this->connections->getServer();

		// Get hostname
		$credentials = $this->connections->getServerCredentials($connection, $server);
		$host        = array_get($credentials, 'host');
		if ($stage) {
			$connection = $stage.'@'.$connection;
		}

		return compact('user', 'branch', 'connection', 'host');
	}

	/**
	 * Get the default message format
	 *
	 * @param string $message The message handle
	 *
	 * @return string
	 */
	abstract protected function getMessageFormat($message);

	/**
	 * Prepare and send a message
	 *
	 * @param Task   $task
	 * @param string $message
	 *
	 * @return void
	 */
	public function prepareThenSend(Task $task, $message)
	{
		// Don't send a notification if pretending to deploy
		if ($task->command->option('pretend')) {
			return;
		}

		// Build message
		$message = $this->getMessageFormat($message);
		$message = preg_replace('#\{([0-9])\}#', '%$1\$s', $message);
		$message = vsprintf($message, $this->getComponents());

		// Send it
		$this->send($message);
	}
}
