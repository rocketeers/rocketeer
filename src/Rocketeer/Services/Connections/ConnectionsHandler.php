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
use Rocketeer\Exceptions\ConnectionException;
use Rocketeer\Services\Connections\Credentials\Keys\ConnectionKey;
use Rocketeer\Traits\HasLocator;

/**
 * Handles, get and return, the various connections/stages
 * and their credentials.
 *
 * @author Maxime Fabre <ehtnam6@gmail.com>
 */
class ConnectionsHandler
{
    use HasLocator;

    /**
     * The current connection.
     *
     * @var ConnectionKey
     */
    protected $current;

    /**
     * The active connections.
     *
     * @var array|null
     */
    protected $activeConnections;

    /**
     * @var array
     */
    protected $cached = [];

    ////////////////////////////////////////////////////////////////////
    //////////////////////// AVAILABLE CONNECTIONS /////////////////////
    ////////////////////////////////////////////////////////////////////

    /**
     * Get the available connections.
     *
     * @return string[][]|string[]
     */
    public function getAvailableConnections()
    {
        // Fetch stored credentials
        $storage = $this->localStorage->get('connections');
        $storage = $this->unifyMultiserversDeclarations($storage);

        // Merge with defaults from config file
        $configuration = $this->config->get('connections');
        $configuration = $this->unifyMultiserversDeclarations($configuration);

        // Merge configurations
        $connections = array_replace_recursive($configuration, $storage);

        return $connections;
    }

    /**
     * Check if a connection has credentials related to it.
     *
     * @param ConnectionKey|string $connection
     *
     * @return bool
     */
    public function isValidConnection($connection)
    {
        $connection = $this->credentials->sanitizeConnection($connection);
        $available = (array) $this->getAvailableConnections();

        return (bool) Arr::get($available, $connection->name.'.servers');
    }

    ////////////////////////////////////////////////////////////////////////////////
    ////////////////////////////// ACTIVE CONNECTIONS //////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////

    /**
     * Get the active connections for this session.
     *
     * @return string[]
     */
    public function getActiveConnections()
    {
        // Get cached resolved connections
        if ($this->activeConnections) {
            return $this->activeConnections;
        }

        // Get default connections and sanitize them
        $connections = (array) $this->config->get('default');
        $connections = array_filter($connections, [$this, 'isValidConnection']);

        // Set current connection as default
        if ($connections) {
            $this->activeConnections = $connections;
        }

        return $connections;
    }

    /**
     * Override the active connections.
     *
     * @param string|string[] $connections
     *
     * @throws ConnectionException
     */
    public function setActiveConnections($connections)
    {
        if (!is_array($connections)) {
            $connections = explode(',', $connections);
        }

        // Sanitize and set connections
        $filtered = array_filter($connections, [$this, 'isValidConnection']);
        if (!$filtered) {
            throw new ConnectionException('Invalid connection(s): '.implode(', ', $connections));
        }

        $this->activeConnections = $filtered;
        $this->current = null;
    }

    ////////////////////////////////////////////////////////////////////////////////
    ///////////////////////////// CURRENT CONNECTIONS //////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////

    /**
     * @return bool
     */
    public function hasCurrentConnection()
    {
        return (bool) $this->current && $this->current->name;
    }

    /**
     * Get the current connection.
     *
     * @return ConnectionKey
     */
    public function getCurrentConnectionKey()
    {
        // If we're in local, enforce the Local connection
        if ($this->rocketeer->isLocal()) {
            $connectionKey = $this->credentials->createConnectionKey('local');
        }

        // If we already have an active connection, return that
        elseif ($this->hasCurrentConnection()) {
            $connectionKey = $this->current;
        }

        // Else bind the default connection
        else {
            $this->current = $connectionKey = $this->credentials->createConnectionKey();
        }

        return $connectionKey;
    }

    /**
     * @return Connections\Connection
     */
    public function getCurrentConnection()
    {
        $key = $this->getCurrentConnectionKey();
        $isConnected = $this->remote->isConnected($key);

        // Create and save to cache
        $connection = $this->remote->make($key);

        // Fire connected event the first time
        if (!$isConnected) {
            $event = 'connected.'.$key->toHandle();
            $this->events->emit($event);
        }

        return $connection;
    }

    /**
     * Set the current connection.
     *
     * @param ConnectionKey|string $connection
     * @param int|null             $server
     */
    public function setCurrentConnection($connection, $server = null)
    {
        $connection = $connection instanceof ConnectionKey ? $connection : $this->credentials->createConnectionKey($connection, $server);
        if (!$this->isValidConnection($connection) || ($this->getCurrentConnectionKey()->is($connection, $server))) {
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
     * Flush active connection(s).
     */
    public function disconnect()
    {
        $this->current = null;
        $this->activeConnections = null;
    }

    ////////////////////////////////////////////////////////////////////
    //////////////////////////////// STAGES ////////////////////////////
    ////////////////////////////////////////////////////////////////////

    /**
     * Set the stage on the current connection.
     *
     * @param string|null $stage
     */
    public function setStage($stage)
    {
        if ($stage === $this->getCurrentConnectionKey()->stage) {
            return;
        }

        $this->current = clone $this->current;
        $this->current->stage = $stage;

        // If we do have a stage, cleanup previous events
        if ($stage) {
            $this->tasks->registerConfiguredEvents();
        }
    }

    /**
     * Get the various stages provided by the User.
     *
     * @return array
     */
    public function getAvailableStages()
    {
        return (array) $this->config->getContextually('stages.stages');
    }

    ////////////////////////////////////////////////////////////////////////////////
    /////////////////////////////////// HELPERS ////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////

    /**
     * Unify a connection's declaration into the servers form.
     *
     * @param array $connection
     *
     * @return array
     */
    protected function unifyMultiserversDeclarations($connection)
    {
        $connection = (array) $connection;
        foreach ($connection as $key => $servers) {
            $servers = Arr::get($servers, 'servers', [$servers]);
            $connection[$key] = ['servers' => array_values($servers)];
        }

        return $connection;
    }
}
