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

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Rocketeer\Exceptions\ConnectionException;
use Rocketeer\Services\Credentials\Keys\ConnectionKeychain;
use Rocketeer\Traits\HasLocator;

/**
 * Handles, get and return, the various connections/stages
 * and their credentials
 *
 * @author Maxime Fabre <ehtnam6@gmail.com>
 */
class ConnectionsHandler
{
    use HasLocator;

    /**
     * The current connection
     *
     * @type ConnectionKeychain
     */
    protected $current;

    /**
     * The connections to use
     *
     * @type array|null
     */
    protected $connections;

    ////////////////////////////////////////////////////////////////////
    //////////////////////////////// STAGES ////////////////////////////
    ////////////////////////////////////////////////////////////////////

    /**
     * Set the stage Tasks will execute on
     *
     * @param string|null $stage
     */
    public function setStage($stage)
    {
        if ($stage === $this->getCurrent()->stage) {
            return;
        }

        $this->current        = clone $this->current;
        $this->current->stage = $stage;

        // If we do have a stage, cleanup previous events
        if ($stage) {
            $this->tasks->registerConfiguredEvents();
        }
    }

    /**
     * Get the various stages provided by the User
     *
     * @return array
     */
    public function getAvailableStages()
    {
        return (array) $this->rocketeer->getOption('stages.stages');
    }

    ////////////////////////////////////////////////////////////////////
    ///////////////////////////// CONNECTIONS //////////////////////////
    ////////////////////////////////////////////////////////////////////

    /**
     * Get the available connections
     *
     * @return string[][]|string[]
     */
    public function getAvailableConnections()
    {
        // Fetch stored credentials
        $storage = $this->localStorage->get('connections');
        $storage = $this->unifyMultiserversDeclarations($storage);

        // Merge with defaults from config file
        $configuration = $this->config->get('rocketeer::connections');
        $configuration = $this->unifyMultiserversDeclarations($configuration);

        // Merge configurations
        $connections = array_replace_recursive($configuration, $storage);

        return $connections;
    }

    /**
     * Check if a connection has credentials related to it
     *
     * @param ConnectionKeychain|string $connection
     *
     * @return boolean
     */
    public function isValidConnection($connection)
    {
        $connection = $this->sanitizeConnection($connection);
        $available  = (array) $this->getAvailableConnections();

        return (bool) Arr::get($available, $connection->name.'.servers');
    }

    /**
     * Get the connection in use
     *
     * @return string[]
     */
    public function getConnections()
    {
        // Get cached resolved connections
        if ($this->connections) {
            return $this->connections;
        }

        // Get default connections and sanitize them
        $connections = (array) $this->config->get('rocketeer::default');
        $connections = array_filter($connections, [$this, 'isValidConnection']);

        // Set current connection as default
        if ($connections) {
            $this->connections = $connections;
        }

        return $connections;
    }

    /**
     * Set the active connections
     *
     * @param string|string[] $connections
     *
     * @throws ConnectionException
     */
    public function setConnections($connections)
    {
        if (!is_array($connections)) {
            $connections = explode(',', $connections);
        }

        // Sanitize and set connections
        $filtered = array_filter($connections, [$this, 'isValidConnection']);
        if (!$filtered) {
            throw new ConnectionException('Invalid connection(s): '.implode(', ', $connections));
        }

        $this->connections = $filtered;
        $this->current     = null;
    }

    /**
     * Get the active connection
     *
     * @return ConnectionKeychain
     */
    public function getCurrent()
    {
        // Return local handle
        if ($this->rocketeer->isLocal()) {
            $handle           = $this->createHandle('local');
            $handle->username = $this->remote->connected() ? $this->remote->connection()->getUsername() : null;
        } elseif ($this->current && $this->current->name) {
            $handle = $this->current;
        } else {
            $this->current = $handle = $this->createHandle();
        }

        return $handle;
    }

    /**
     * Set the current connection
     *
     * @param ConnectionKeychain|string $connection
     * @param integer                   $server
     */
    public function setConnection($connection, $server = null)
    {
        $connection = $connection instanceof ConnectionKeychain ? $connection : $this->createHandle($connection, $server);
        if (!$this->isValidConnection($connection) || ($this->getCurrent()->is($connection))) {
            return;
        }

        if ($server) {
            $connection->server = $server;
        }

        // Set the connection
        $this->current = $connection;

        // Update events
        $this->tasks->registerConfiguredEvents();
    }

    /**
     * Flush active connection(s)
     */
    public function disconnect()
    {
        $this->current     = null;
        $this->connections = null;
    }
}
