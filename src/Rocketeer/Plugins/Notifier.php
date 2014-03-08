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

use Rocketeer\Traits\Plugin;
use Rocketeer\TasksHandler;

/**
 * A base class for notification services to extends
 */
abstract class Notifier extends Plugin
{
	/**
	 * Register Tasks with Rocketeer
	 *
	 * @param TasksHandler $queue
	 *
	 * @return void
	 */
	public function onQueue(TasksHandler $queue)
	{
		$me = $this;
		$queue->after('deploy', function ($task) use ($me) {

			// Don't send a notification if pretending to deploy
			if ($task->command->option('pretend')) {
				return;
			}

			// Build message and send it
			$message = $me->makeMessage();
			$me->send($message);

		}, -10);
	}

	/**
	 * Send a given message
	 *
	 * @param Task   $task
	 * @param string $message
	 *
	 * @return void
	 */
	abstract protected function send($message);

	////////////////////////////////////////////////////////////////////
	/////////////////////////////// MESSAGE ////////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Get the message's components
	 *
	 * @return array
	 */
	protected function getComponents()
	{
		// Get user name
		$user = $this->server->getValue('notifier.name');
		if (!$user) {
			$user = $this->command->ask('Who is deploying ?');
			$this->server->setValue('notifier.name', $user);
		}

		// Get what was deployed
		$branch     = $this->rocketeer->getRepositoryBranch();
		$stage      = $this->rocketeer->getStage();
		$connection = $this->rocketeer->getConnection();

		// Get hostname
		$credentials = array_get($this->rocketeer->getAvailableConnections(), $connection);
		$host        = array_get($credentials, 'host');
		if ($stage) {
			$connection = $stage.'@'.$connection;
		}

		return compact('user', 'branch', 'connection', 'host');
	}

	/**
	 * Get the default message format
	 *
	 * @return string
	 */
	abstract protected function getMessageFormat();

	/**
	 * Build the message from the components
	 *
	 * @return string
	 */
	public function makeMessage()
	{
		$message = $this->getMessageFormat();
		$message = preg_replace('#\{([0-9])\}#', '%$1\$s', $message);
		$message = vsprintf($message, $this->getComponents());

		return $message;
	}
}
