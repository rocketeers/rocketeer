<?php

/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace Rocketeer\Services\Tasks;

use Closure;
use Exception;
use KzykHys\Parallel\Parallel;
use LogicException;
use Rocketeer\Services\Connections\Connections\ConnectionInterface;
use Rocketeer\Traits\ContainerAwareTrait;
use Rocketeer\Traits\Properties\HasHistoryTrait;

/**
 * Handles running an array of tasks sequentially
 * or in parallel.
 */
class TasksQueue
{
    use ContainerAwareTrait;
    use HasHistoryTrait;

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
            $this->connections->setActiveConnections($connections);
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
        $tasks = is_array($tasks) ? $tasks : [$tasks];
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
        /** @var ConnectionInterface[] $connections */
        $connections = $this->connections->getActiveConnections();
        foreach ($connections as $connection) {
            $connectionKey = $connection->getConnectionKey();
            $stages = $this->getStages($connectionKey);

            // Add job to pipeline
            foreach ($stages as $stage) {
                $connectionKey->stage = $stage;

                $pipeline[] = new Job([
                    'connectionKey' => clone $connectionKey,
                    'queue' => $queue,
                ]);
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
        $connectionKey = $job->connectionKey;
        $this->connections->setCurrentConnection($connectionKey);

        foreach ($job->queue as $key => $task) {
            if ($task->usesStages()) {
                $stage = $task->usesStages() ? $connectionKey->stage : null;
                $this->connections->setStage($stage);
            }

            // Check if the current server can run the task
            if (!$this->connections->getCurrentConnection()->isCompatibleWith($task)) {
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
            $results[$key] = $this->bash->checkResults($task());
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
        $this->connections->setCurrentConnection($connection);

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
