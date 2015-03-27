<?php

/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Rocketeer\Services\Connections;

use Rocketeer\Services\Tasks\Job;
use Rocketeer\Traits\HasLocator;

class Coordinator
{
    use HasLocator;

    /**
     * The status of each server.
     *
     * @type array
     */
    protected $statuses = [];

    /**
     * Server is idle.
     */
    const IDLE = 0;

    /**
     * Server is waiting for further instruction.
     */
    const WAITING = 1;

    /**
     * Server is done deploying.
     */
    const DONE = 3;

    //////////////////////////////////////////////////////////////////////
    /////////////////////////////// EVENTS ///////////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * Execute a listener when all servers are at the same point.
     *
     * @param string   $event
     * @param callable $listener
     */
    public function whenAllServersReadyTo($event, callable $listener)
    {
        // Set status
        $event  = $this->getPromiseHandle($event);
        $handle = (string) $this->connections->getCurrentConnection();

        // Initiate statuses
        if (!isset($this->statuses[$event])) {
            $this->statuses[$event] = [];
        }

        // Bind listener
        $this->statuses[$event][$handle] = self::WAITING;
        $this->registerJobListener($event, $listener);

        // Fire when all servers are ready
        if ($this->allServerAre($event, static::WAITING)) {
            $this->events->emit($event);
        }
    }

    //////////////////////////////////////////////////////////////////////
    ////////////////////////////// STATUSES //////////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * Assert whether all servers are at a particular state.
     *
     * @param     $event
     * @param int $expected
     *
     * @return bool
     */
    public function allServerAre($event, $expected)
    {
        $targets  = $this->computeNumberOfTargets();
        $statuses = array_filter($this->statuses[$event], function ($server) use ($expected) {
            return $server === $expected;
        });

        return $targets === count($statuses);
    }

    /**
     * Update a status.
     *
     * @param string $event
     * @param int    $status
     */
    public function setStatus($event, $status)
    {
        $handle = (string) $this->connections->getCurrentConnection();

        $this->statuses[$event][$handle] = $status;
    }

    /**
     * Get the status of all servers.
     *
     * @return array
     */
    public function getStatuses()
    {
        return $this->statuses;
    }

    //////////////////////////////////////////////////////////////////////
    ////////////////////////////// HELPERS ///////////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * @param string $event
     *
     * @return string
     */
    protected function getPromiseHandle($event)
    {
        return 'rocketeer.promises.'.$event;
    }

    /**
     * @param          $event
     * @param callable $listener
     */
    protected function registerJobListener($event, callable $listener)
    {
        $connection = $this->connections->getCurrentConnection();

        $job = new Job([
            'connection' => $connection,
            'queue'      => $this->builder->buildTasks([$listener]),
        ]);

        $this->events->addListener($event, function () use ($job) {
            $this->queue->executeJob($job);
        }, microtime(true));
    }

    /**
     * Get the number of servers to wait for
     * before triggering a promise.
     *
     * @return int
     */
    protected function computeNumberOfTargets()
    {
        $targets = 0;

        $connections = $this->connections->getConnections();
        foreach ($connections as $connection) {
            $stages  = $this->connections->getAvailableStages();
            $servers = $this->credentials->getConnectionCredentials($connection);
            $targets += count($servers) * count($stages);
        }

        return $targets;
    }
}
