<?php
/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Rocketeer\Services\Tasks;

use Closure;
use Exception;
use KzykHys\Parallel\Parallel;
use Rocketeer\Connection;
use Rocketeer\Traits\HasHistory;
use Rocketeer\Traits\HasLocator;

/**
 * Handles running an array of tasks sequentially
 * or in parallel
 *
 * @author Maxime Fabre <ehtnam6@gmail.com>
 */
class TasksQueue
{
	use HasLocator;
	use HasHistory;

	/**
	 * @type Parallel
	 */
	protected $parallel;

	/**
	 * A list of Tasks to execute
	 *
	 * @var array
	 */
	protected $tasks;

	/**
	 * The Remote connection
	 *
	 * @var Connection
	 */
	protected $remote;

	/**
	 * @param Parallel $parallel
	 */
	public function setParallel($parallel)
	{
		$this->parallel = $parallel;
	}

	////////////////////////////////////////////////////////////////////
	////////////////////////////// SHORTCUTS ///////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Execute Tasks on the default connection
	 *
	 * @param string|array|Closure $queue
	 * @param string|string[]|null $connections
	 *
	 * @return boolean
	 */
	public function execute($queue, $connections = null)
	{
		if ($connections) {
			$this->connections->setConnections($connections);
		}

		return $this->run($queue);
	}

	/**
	 * Execute Tasks on various connections
	 *
	 * @param string|string[]      $connections
	 * @param string|array|Closure $queue
	 *
	 * @return boolean
	 */
	public function on($connections, $queue)
	{
		return $this->execute($queue, $connections);
	}

	////////////////////////////////////////////////////////////////////
	//////////////////////////////// QUEUE /////////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Run the queue
	 * Run an array of Tasks instances on the various
	 * connections and stages provided
	 *
	 * @param string|array $tasks An array of tasks
	 *
	 * @throws Exception
	 * @return boolean
	 */
	public function run($tasks)
	{
		$tasks    = (array) $tasks;
		$queue    = $this->builder->buildTasks($tasks);
		$pipeline = $this->buildPipeline($queue);

		if ($this->command->option('parallel')) {
			return $this->runAsynchronously($pipeline);
		} else {
			return $this->runSynchronously($pipeline);
		}
	}

	/**
	 * Run the queue, taking into account the stage
	 *
	 * @param Job $job
	 *
	 * @return boolean
	 */
	protected function executeJob(Job $job)
	{
		// Set proper server
		$this->connections->setConnection($job->connection, $job->server);

		foreach ($job->queue as $task) {
			$currentStage = $task->usesStages() ? $job->stage : null;
			$this->connections->setStage($currentStage);

			// Here we fire the task, save its
			// output and return its status
			$state = $task->fire();
			$this->toOutput($state);

			// If the task didn't finish, display what the error was
			if ($task->wasHalted() or $state === false) {
				$this->command->error('The tasks queue was canceled by task "'.$task->getName().'"');

				return false;
			}
		}

		return true;
	}

	/**
	 * Build a pipeline of jobs for Parallel to execute
	 *
	 * @param array $queue
	 *
	 * @return callable[]
	 */
	protected function buildPipeline(array $queue)
	{
		// First we'll build the queue
		$pipeline = [];

		// Get the connections to execute the tasks on
		$connections = (array) $this->connections->getConnections();
		foreach ($connections as $connection) {
			$servers = $this->connections->getConnectionCredentials($connection);
			$stages  = $this->getStages($connection);

			// Add job to pipeline
			foreach ($servers as $server => $credentials) {
				foreach ($stages as $stage) {
					$pipeline[] = new Job(array(
						'connection' => $connection,
						'server'     => $server,
						'stage'      => $stage,
						'queue'      => $queue,
					));
				}
			}
		}

		// Wrap job in closure pipeline
		foreach ($pipeline as $key => $job) {
			$pipeline[$key] = function () use ($job) {
				return $this->executeJob($job);
			};
		}

		return $pipeline;
	}

	//////////////////////////////////////////////////////////////////////
	////////////////////////////// RUNNERS ///////////////////////////////
	//////////////////////////////////////////////////////////////////////

	/**
	 * Run the pipeline in order.
	 * As long as the previous entry didn't fail, continue
	 *
	 * @param callable[] $pipeline
	 *
	 * @return boolean
	 */
	protected function runSynchronously(array $pipeline)
	{
		foreach ($pipeline as $task) {
			if (!$task()) {
				return false;
			}
		}

		return true;
	}

	/**
	 * @param callable[] $pipeline
	 *
	 * @return boolean
	 * @throws \Exception
	 */
	protected function runAsynchronously(array $pipeline)
	{
		if (!extension_loaded('pcntl')) {
			throw new Exception('Parallel jobs require the PCNTL extension');
		}

		$this->parallel = $this->parallel ?: new Parallel();
		$this->parallel->run($pipeline);

		return true;
	}

	////////////////////////////////////////////////////////////////////
	//////////////////////////////// STAGES ////////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Get the stages of a connection
	 *
	 * @param string $connection
	 *
	 * @return array
	 */
	public function getStages($connection)
	{
		$this->connections->setConnection($connection);

		$stage = $this->rocketeer->getOption('stages.default');
		if ($this->hasCommand()) {
			$stage = $this->command->option('stage') ?: $stage;
		}

		// Return all stages if "all"
		if ($stage == 'all' or !$stage) {
			$stage = $this->connections->getStages();
		}

		// Sanitize and filter
		$stages = (array) $stage;
		$stages = array_filter($stages, [$this, 'isValidStage']);

		return $stages ?: [null];
	}

	/**
	 * Check if a stage is valid
	 *
	 * @param string $stage
	 *
	 * @return boolean
	 */
	public function isValidStage($stage)
	{
		return in_array($stage, $this->connections->getStages());
	}
}
