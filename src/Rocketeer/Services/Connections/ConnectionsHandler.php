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

namespace Rocketeer\Services\Connections;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Rocketeer\Services\Connections\Connections\AbstractConnection;
use Rocketeer\Services\Connections\Connections\Connection;
use Rocketeer\Services\Connections\Connections\ConnectionInterface;
use Rocketeer\Services\Connections\Credentials\Keys\ConnectionKey;
use Rocketeer\Traits\ContainerAwareTrait;

/**
 * Handles, get and return, the various connections/stages
 * and their credentials.
 */
class ConnectionsHandler
{
    use ContainerAwareTrait;

    /**
     * @var Collection<ConnectionInterface>
     */
    protected $available;

    /**
     * @var string
     */
    protected $current;

    ////////////////////////////////////////////////////////////////////
    //////////////////////// AVAILABLE CONNECTIONS /////////////////////
    ////////////////////////////////////////////////////////////////////

    /**
     * @return string[]
     */
    public function getDefaultConnectionsHandles()
    {
        return (array) $this->config->get('default');
    }

    /**
     * Get the available connections.
     *
     * @return array
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
     * @return Collection<Connection>
     */
    public function getConnections()
    {
        // Convert to ConnectionKey/Connection instances
        if (!$this->available) {
            $connections = [];

            $available = $this->getAvailableConnections();
            foreach ($available as $name => $connection) {
                // Skip connections without any defined servers
                $servers = $connection['servers'];
                if (!$servers) {
                    continue;
                }

                foreach ($servers as $key => &$server) {
                    foreach ($server as $subkey => &$value) {
                        $option = $this->getOption($subkey, true);
                        $value = !is_null($option) ? $option : $value;
                    }

                    $connectionKey = new ConnectionKey([
                        'name' => $name,
                        'server' => $key,
                        'servers' => $servers,
                    ]);

                    // Create connection
                    $connection = $this->remote->make($connectionKey);
                    $connection->setActive($this->isConnectionActive($connection));

                    $connections[$connectionKey->toHandle()] = $connection;
                }
            }

            // Add local and dummy connections
            $connections['local'] = $this->remote->make(new ConnectionKey([
                'name' => 'local',
                'server' => 0,
                'servers' => [['host' => 'localhost']],
            ]));

            $connections['dummy'] = $this->remote->make(new ConnectionKey([
                'name' => 'dummy',
                'server' => 0,
                'servers' => [
                    [
                        'host' => 'localhost',
                        'root_directory' => '/tmp/rocketeer',
                    ],
                ],
            ]));

            $this->available = new Collection($connections);
        }

        return $this->available;
    }

    /**
     * @param ConnectionKey|string $connection
     * @param int|null             $server
     *
     * @throws ConnectionException
     *
     * @return Connection
     */
    public function getConnection($connection, $server = null)
    {
        $connectionKey = $this->credentials->sanitizeConnection($connection, $server);
        $handle = $connectionKey->toHandle();

        $connections = $this->getConnections();
        if (!$connections->has($handle)) {
            throw new ConnectionException('Invalid connection: '.$handle);
        }

        return $connections[$handle];
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
     * @return Collection<Connection>
     */
    public function getActiveConnections()
    {
        return $this->getConnections()->filter(function (ConnectionInterface $connection) {
            return $this->isConnectionActive($connection);
        });
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

        $this->available = $this->getConnections()->map(function (ConnectionInterface $connection) use ($connections) {
            $connectionKey = $connection->getConnectionKey();
            $handle = $connectionKey->toHandle();

            $connection->setActive(in_array($handle, $connections, true) || in_array($connectionKey->name, $connections, true));
            $connection->setCurrent(false);

            return $connection;
        });
    }

    ////////////////////////////////////////////////////////////////////////////////
    ///////////////////////////// CURRENT CONNECTIONS //////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////

    /**
     * @return AbstractConnection
     */
    public function getCurrentConnection()
    {
        if ($this->rocketeer->isLocal()) {
            return $this->getConnection('local');
        }

        $connections = $this->getConnections();
        if ($this->current && $current = $connections->get($this->current)) {
            return $current;
        }

        /** @var ConnectionInterface $connection */
        $connection = $connections->first(function (ConnectionInterface $connection) {
            return $connection->isCurrent();
        });

        // If no connection is marked as current, get the first active one
        if (!$connection) {
            $connection = $this->getActiveConnections()->first();
        }

        // Fire connected event the first time
        $handle = $connection ? $connection->getConnectionKey()->toHandle() : null;
        if ($connection && !$connection->isConnected()) {
            $event = 'connected.'.$handle;
            $this->events->emit($event);
        }

        // Cache which connection is the first
        $this->current = $handle;

        return $connection;
    }

    /**
     * Get the current connection.
     *
     * @return ConnectionKey
     */
    public function getCurrentConnectionKey()
    {
        $current = $this->getCurrentConnection();

        return $current ? $current->getConnectionKey() : $this->credentials->createConnectionKey();
    }

    /**
     * Set the current connection.
     *
     * @param ConnectionKey|string $connection
     * @param int|null             $server
     */
    public function setCurrentConnection($connection = null, $server = null)
    {
        $connectionKey = $connection instanceof ConnectionKey ? $connection : $this->credentials->createConnectionKey($connection, $server);
        if ($this->current === $connectionKey->toHandle()) {
            return;
        }

        $this->current = $connectionKey->toHandle();
        $this->available = $this->getConnections()->map(function (ConnectionInterface $connection) use ($connectionKey) {
            $isCurrent = $connectionKey->is($connection->getConnectionKey());
            $connection->setCurrent($isCurrent);

            return $connection;
        });

        // Update events
        $this->bootstrapper->bootstrapUserCode();
    }

    /**
     * @return bool
     */
    public function hasCurrentConnection()
    {
        return (bool) $this->getCurrentConnection();
    }

    /**
     * @param string   $name
     * @param int|null $server
     *
     * @return bool
     */
    public function is($name, $server = null)
    {
        return $this->getCurrentConnectionKey()->is($name, $server);
    }

    /**
     * Flush active connection(s).
     */
    public function disconnect()
    {
        $this->current = null;
        $this->available = [];
    }

    ////////////////////////////////////////////////////////////////////
    //////////////////////////////// STAGES ////////////////////////////
    ////////////////////////////////////////////////////////////////////

    /**
     * Get the various stages provided by the User.
     *
     * @return array
     */
    public function getAvailableStages()
    {
        return (array) $this->config->getContextually('stages.stages');
    }

    /**
     * Set the stage on the current connection.
     *
     * @param string|null $stage
     */
    public function setStage($stage)
    {
        $connectionKey = $this->getCurrentConnectionKey();
        if ($stage === $connectionKey->stage) {
            return;
        }

        $connectionKey->stage = $stage;
        $this->getConnections()->map(function (ConnectionInterface $connection) use ($connectionKey) {
            if ($connection->isCurrent() || $connection->getConnectionKey()->is($connectionKey)) {
                $connection->setConnectionKey($connectionKey);
            }

            return $connection;
        });

        // If we do have a stage, cleanup previous events
        if ($stage) {
            $this->bootstrapper->bootstrapUserCode();
        }
    }

    ////////////////////////////////////////////////////////////////////////////////
    /////////////////////////////////// HELPERS ////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////

    /**
     * @param ConnectionInterface $connection
     *
     * @return bool
     */
    protected function isConnectionActive(ConnectionInterface $connection)
    {
        $connectionKey = $connection->getConnectionKey();
        $defaults = $this->getDefaultConnectionsHandles();

        return
            in_array($connectionKey->toHandle(), $defaults, true) ||
            in_array($connectionKey->name, $defaults, true) ||
            $connection->isActive();
    }

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
            $servers = array_values($servers);
            foreach ($servers as &$server) {
                if (array_key_exists('key', $server)) {
                    $server['key'] = str_replace('~/', $this->paths->getUserHomeFolder().'/', $server['key']);
                }
            }

            $connection[$key] = ['servers' => $servers];
        }

        return $connection;
    }
}
