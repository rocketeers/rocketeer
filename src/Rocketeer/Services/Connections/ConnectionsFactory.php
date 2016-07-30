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

use Rocketeer\Services\Connections\Connections\Connection;
use Rocketeer\Services\Connections\Connections\LocalConnection;
use Rocketeer\Services\Connections\Credentials\Keys\ConnectionKey;

/**
 * Handle creation and caching of connections.
 */
class ConnectionsFactory
{
    /**
     * Build a Connection instance from a ConnectionKey.
     *
     * @param ConnectionKey $connectionKey
     *
     * @return Connection
     */
    public function make(ConnectionKey $connectionKey)
    {
        $type = $connectionKey->host === 'localhost' ? LocalConnection::class : Connection::class;

        return new $type($connectionKey);
    }
}
