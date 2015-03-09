<?php

/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Rocketeer\Services\Credentials\Keychains;

use Illuminate\Support\Arr;
use Rocketeer\Services\Credentials\Keys\ConnectionKey;

/**
 * Finds credentials and informations about connections.
 *
 * @mixin \Rocketeer\Services\Credentials\CredentialsHandler
 *
 * @author Maxime Fabre <ehtnam6@gmail.com>
 */
trait ConnectionsKeychain
{
    /**
     * Get the credentials for a particular connection.
     *
     * @param ConnectionKey|string|null $connection
     *
     * @return string[][]
     */
    public function getConnectionCredentials($connection = null)
    {
        $connection = $this->sanitizeConnection($connection);
        $available  = $this->connections->getAvailableConnections();

        // Get and filter servers
        $servers = Arr::get($available, $connection->name.'.servers');
        if ($this->hasCommand() && $allowed = $this->command->option('server')) {
            $allowed = explode(',', $allowed);
            $servers = array_intersect_key((array) $servers, array_flip($allowed));
        }

        return $servers;
    }

    /**
     * Get the credentials for as server.
     *
     * @param ConnectionKey|string|null $connection
     * @param int                       $server
     *
     * @return array
     */
    public function getServerCredentials($connection = null, $server = 0)
    {
        return $this->sanitizeConnection($connection, $server)->getServerCredentials();
    }

    /**
     * Sync Rocketeer's credentials with Laravel's.
     *
     * @param ConnectionKey|null $connection
     * @param array              $credentials
     */
    public function syncConnectionCredentials(ConnectionKey $connection = null, array $credentials = [])
    {
        // Store credentials if any
        if ($credentials) {
            $filtered = $this->filterUnsavableCredentials($connection, $credentials);
            $this->localStorage->set('connections.'.$connection.'.servers.'.$connection->server, $filtered);

            $this->config->set('connections.'.$connection->toHandle(), $credentials);
        }

        // Get connection
        $connection  = $this->sanitizeConnection($connection);
        $credentials = $credentials ?: $this->getConnectionCredentials($connection);

        $this->config->set('connections.'.$connection->name, $credentials);
    }

    //////////////////////////////////////////////////////////////////////
    ////////////////////////////// HANDLES ///////////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * Build the current connection's handle.
     *
     * @param ConnectionKey|string|null $connection
     * @param int|null                  $server
     * @param string|null               $stage
     *
     * @return ConnectionKey
     */
    public function createConnectionKey($connection = null, $server = null, $stage = null)
    {
        if ($connection instanceof ConnectionKey) {
            return $connection;
        }

        // Concatenate
        $handle = new ConnectionKey([
            'name'   => $connection ?: Arr::get($this->connections->getConnections(), 0),
            'server' => $server ?: 0,
            'stage'  => $stage ?: null,
        ]);

        // Populate credentials
        $handle->servers = $this->getConnectionCredentials($handle);

        return $handle;
    }

    /**
     * Transform an instance/credentials into a ConnectionKey.
     *
     * @param ConnectionKey|string|null $connection
     * @param int|null                  $server
     *
     * @return ConnectionKey
     */
    public function sanitizeConnection($connection = null, $server = null)
    {
        if (!$connection) {
            return $this->connections->getCurrentConnection();
        }

        return $connection instanceof ConnectionKey ? $connection : $this->createConnectionKey($connection, $server);
    }

    //////////////////////////////////////////////////////////////////////
    ////////////////////////////// HELPERS ///////////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * Filter the credentials and remove the ones that
     * can't be saved to disk.
     *
     * @param ConnectionKey $connection
     * @param array         $credentials
     *
     * @return string[]
     */
    protected function filterUnsavableCredentials(ConnectionKey $connection, $credentials)
    {
        $defined = $this->getServerCredentials($connection);
        foreach ($credentials as $key => $value) {
            if (array_get($defined, $key) === true) {
                unset($credentials[$key]);
            }
        }

        return $credentials;
    }
}
