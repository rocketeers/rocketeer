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

use Rocketeer\Interfaces\CredentialsExceptionInterface;
use Rocketeer\Services\Connections\Connections\Connection;
use Rocketeer\Services\Connections\Credentials\Keys\ConnectionKey;

/**
 * Handle creation and caching of connections.
 *
 * @author Maxime Fabre <ehtnam6@gmail.com>
 * @author Taylor Otwell <taylorotwell@gmail.com>
 */
class ConnectionsFactory
{
    /**
     * @var array
     */
    protected $connected = [];

    /**
     * Build a Connection instance from a ConnectionKey.
     *
     * @param ConnectionKey $connectionKey
     *
     * @throws CredentialsExceptionInterface
     *
     * @return Connection
     */
    public function make(ConnectionKey $connectionKey)
    {
        $handle = (string) $connectionKey->toHandle();

        // Check the cache for already resolved connection
        if (isset($this->connected[$handle])) {
            return $this->connected[$handle];
        }

        // Create connection
        $connection = new Connection($connectionKey);

        // Save resolved connection
        $this->connected[$handle] = $connection;

        return $connection;
    }

    /**
     * Check if we already have an opened connection somewhere.
     *
     * @param ConnectionKey $key
     *
     * @return bool
     */
    public function isConnected(ConnectionKey $key)
    {
        return array_key_exists((string) $key->toHandle(), $this->connected);
    }

    /**
     * Disconnect from all connections.
     */
    public function disconnect()
    {
        $this->connected = [];
    }
}
