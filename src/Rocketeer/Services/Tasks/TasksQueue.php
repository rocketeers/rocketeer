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
use LogicException;
use Rocketeer\Connection;
use Rocketeer\Traits\HasLocator;
use Rocketeer\Traits\Properties\HasHistory;

/**
 * Handles running an array of tasks sequentially
 * or in parallel.
 *
 * @author Maxime Fabre <ehtnam6@gmail.com>
 */
class TasksQueue
{
    use HasLocator;
    use HasHistory;

    /**
     * @var Parallel
     */
    protected $parallel;

    /**
     * A list of Tasks to execute.
     *
     * @var array
     */
    protected $tasks;

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
     * Execute Tasks on the default connection and
     * return their output.
     *
     * @param string|array|Closure $queue
     * @param string|string[]|null $connections
     *
     * @return string|string[]|false
     */
    public function execute($queue, $connections = null)
    {
        if ($connections) {
            $this->connections->setConnections($connections);
        }

        // Run tasks
        $this->run($queue);
        $history = $this->history->getFlattenedOutput();

        return end($history);
    }

    /**
     * Execute Tasks on various connections.
     *
     * @param string|string[]      $connections
     * @param string|array|Closure $queue
     *
     * @return string|string[]|false
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
     * connections and stages provided.
     *
     * @param string|array $tasks An array of tasks
     *
     * @throws Exception
     *
     * @return Pipeline
     */
    public function run($tasks)
    {
        $tasks = (array) $tasks;
        $queue = $this->builder->buildTasks($tasks);
        $pipeline = $this->buildPipeline($queue);

        // Wrap job in closure pipeline
        foreach ($pipeline as $key => $job) {
            $pipeline[$key] = function () use ($job) {
                return $this->executeJob($job);
            };
        }

        // Run the tasks and store the results
        if ($this->getOption('parallel')) {
            $pipeline = $this->runAsynchronously($pipeline);
        } else {
            $pipeline = $this->runSynchronously($pipeline);
        }

        return $pipeline;
    }

    /**
     * Build a pipeline of jobs for Parallel to execute.
     *
     * @param array $queue
     *
     * @return Pipeline
     */
    public function buildPipeline(array $queue)
    {
        // First we'll build the queue
        $pipeline = new Pipeline();

        // Get the connections to execute the tasks on
        $connections = (array) $this->connections->getConnections();
        foreach ($connections as $connection) {
            $connection = $this->credentials->createConnectionKey($connection);
            $stages = $this->getStages($connection);

            // Add job to pipeline
            foreach ($connection->servers as $server => $credentials) {
                foreach ($stages as $stage) {
                    $connection->server = $server;
                    $connection->stage = $stage;

                    $pipeline[] = new Job([
                        'connection' => clone $connection,
                        'queue' => $queue,
                    ]);
                }
            }
        }

        return $pipeline;
    }

    /**
     * Run the queue, taking into account the stage.
     *
     * @param Job $job
     *
     * @return bool
     */
    public function executeJob(Job $job)
    {
        // Set proper server
        $connection = $job->connection;
        $this->connections->setConnection($connection);

        foreach ($job->queue as $key => $task) {
            if ($task->usesStages()) {
                $stage = $task->usesStages() ? $connection->stage : null;
                $this->connections->setStage($stage);
            }

            // Check if the current server can run the task
            if (!$this->remote->connection()->isCompatibleWith($task)) {
                continue;
            }

            // Here we fire the task, save its
            // output and return its status
            $state = $task->fire();
            $this->toOutput($state);

            // If the task didn't finish, display what the error was
            if ($task->wasHalted() || $state === false) {
                $this->explainer->error('The tasks queue was canceled by task "'.$task->getName().'"');

                return false;
            }
        }

        return true;
    }

    //////////////////////////////////////////////////////////////////////
    ////////////////////////////// RUNNERS ///////////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * Run the pipeline in order.
     * As long as the previous entry didn't fail, continue.
     *
     * @param Pipeline $pipeline
     *
     * @return Pipeline
     */
    protected function runSynchronously(Pipeline $pipeline)
    {
        $results = [];

        /** @var Closure $task */
        foreach ($pipeline as $key => $task) {
            $results[$key] = $task();
            if (!$results[$key]) {
                break;
            }
        }

        // Update Pipeline results
        $pipeline->setResults($results);

        return $pipeline;
    }

    /**
     * Run the pipeline in parallel order.
     *
     * @param Pipeline $pipeline
     *
     * @throws \Exception
     *
     * @return Pipeline
     */
    protected function runAsynchronously(Pipeline $pipeline)
    {
        $this->parallel = $this->parallel ?: new Parallel();

        // Check if supported
        if (!$this->parallel->isSupported()) {
            throw new Exception('Parallel jobs require the PCNTL extension');
        }

        try {
            $this->parallel = $this->parallel ?: new Parallel();
            $results = $this->parallel->values($pipeline->all());
            $pipeline->setResults($results);
        } catch (LogicException $exception) {
            return $this->runSynchronously($pipeline);
        }

        return $pipeline;
    }

    ////////////////////////////////////////////////////////////////////
    //////////////////////////////// STAGES ////////////////////////////
    ////////////////////////////////////////////////////////////////////

    /**
     * Get the stages of a connection.
     *
     * @param string $connection
     *
     * @return array
     */
    public function getStages($connection)
    {
        $this->connections->setConnection($connection);

        $stage = $this->config->getContextually('stages.default');
        if ($this->hasCommand()) {
            $stage = $this->getOption('stage') ?: $stage;
        }

        // Return all stages if "all"
        if ($stage === 'all' || !$stage) {
            $stage = $this->connections->getAvailableStages();
        }

        // Sanitize and filter
        $stages = (array) $stage;
        $stages = array_filter($stages, [$this, 'isValidStage']);

        return $stages ?: [null];
    }

    /**
     * Check if a stage is valid.
     *
     * @param string $stage
     *
     * @return bool
     */
    public function isValidStage($stage)
    {
        return in_array($stage, $this->connections->getAvailableStages(), true);
    }
}
