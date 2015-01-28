<?php
namespace Rocketeer\Services\Connections;

use Rocketeer\Abstracts\AbstractTask;
use Rocketeer\Traits\HasLocator;

class Coordinator
{
    use HasLocator;

    /**
     * The status of each server
     *
     * @type array
     */
    protected $statuses = [];

    /**
     * Server is idle
     */
    const IDLE = 0;

    /**
     * Server is waiting for further instruction
     */
    const WAITING = 1;

    /**
     * Server is done deploying
     */
    const DONE = 3;

    //////////////////////////////////////////////////////////////////////
    /////////////////////////////// EVENTS ///////////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * Trigger for when a server gets right to before symlink
     *
     * @param AbstractTask $task
     */
    public function beforeSymlink(AbstractTask $task)
    {
        $handle                  = $this->connections->getHandle();
        $this->statuses[$handle] = static::WAITING;
    }

    //////////////////////////////////////////////////////////////////////
    ////////////////////////////// STATUSES //////////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * Get the status of all servers
     *
     * @return array
     */
    public function getStatuses()
    {
        return $this->statuses;
    }

    /**
     * Get the status of a server
     *
     * @param string       $connection
     * @param integer|null $server
     * @param string|null  $stage
     *
     * @return integer
     */
    public function getStatus($connection, $server = null, $stage = null)
    {
        $handle = $this->connections->getHandle($connection, $server, $stage);

        return array_get($this->statuses, $handle, static::IDLE);
    }
}
