<?php
namespace Rocketeer\Services\Credentials\Keychains;

use Illuminate\Support\Arr;
use Rocketeer\Services\Credentials\Keys\ConnectionKeychain;

trait ConnectionsKeychain
{
    /**
     * Get the credentials for a particular connection
     *
     * @param ConnectionKeychain|string|null $connection
     *
     * @return string[][]
     */
    public function getConnectionCredentials($connection = null)
    {
        $connection = $this->sanitizeConnection($connection);
        $available  = $this->getAvailableConnections();

        // Get and filter servers
        $servers = Arr::get($available, $connection->name.'.servers');
        if ($this->hasCommand() && $allowed = $this->command->option('server')) {
            $allowed = explode(',', $allowed);
            $servers = array_intersect_key((array) $servers, array_flip($allowed));
        }

        return $servers;
    }

    /**
     * Get the credentials for as server
     *
     * @param ConnectionKeychain|string|null $connection
     * @param integer                        $server
     *
     * @return array
     */
    public function getServerCredentials($connection = null, $server = 0)
    {
        return $this->sanitizeConnection($connection, $server)->getServerCredentials();
    }

    /**
     * Sync Rocketeer's credentials with Laravel's
     *
     * @param ConnectionKeychain|null $connection
     * @param array                   $credentials
     */
    public function syncConnectionCredentials(ConnectionKeychain $connection = null, array $credentials = [])
    {
        // Store credentials if any
        if ($credentials) {
            $filtered = $this->filterUnsavableCredentials($connection, $credentials);
            $this->localStorage->set('connections.'.$connection.'.servers.'.$connection->server, $filtered);

            $this->config->set('rocketeer::connections.'.$connection->toHandle(), $credentials);
        }

        // Get connection
        $connection  = $this->sanitizeConnection($connection);
        $credentials = $credentials ?: $this->getConnectionCredentials($connection);

        $this->config->set('rocketeer::connections.'.$connection->name, $credentials);
    }

    //////////////////////////////////////////////////////////////////////
    ////////////////////////////// HANDLES ///////////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * Build the current connection's handle
     *
     * @param ConnectionKeychain|string|null $connection
     * @param integer|null                   $server
     * @param string|null                    $stage
     *
     * @return ConnectionKeychain
     */
    public function createHandle($connection = null, $server = null, $stage = null)
    {
        if ($connection instanceof ConnectionKeychain) {
            return $connection;
        }

        // Get identifiers
        $connection = $connection ?: Arr::get($this->getConnections(), 0);
        $server     = $server ?: 0;
        $stage      = $stage ?: null;

        // Concatenate
        $handle = new ConnectionKeychain($connection, $server, $stage);

        // Populate credentials
        $handle->servers = $this->getConnectionCredentials($handle);

        return $handle;
    }

    /**
     * Transform an instance/credentials into a ConnectionKeychain
     *
     * @param ConnectionKeychain|string|null $connection
     * @param integer|null                   $server
     *
     * @return ConnectionKeychain
     */
    protected function sanitizeConnection($connection = null, $server = null)
    {
        if (!$connection) {
            return $this->getCurrent();
        }

        return $connection instanceof ConnectionKeychain ? $connection : $this->createHandle($connection, $server);
    }

    //////////////////////////////////////////////////////////////////////
    ////////////////////////////// HELPERS ///////////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * Filter the credentials and remove the ones that
     * can't be saved to disk
     *
     * @param ConnectionKeychain $connection
     * @param array              $credentials
     *
     * @return string[]
     */
    protected function filterUnsavableCredentials(ConnectionKeychain $connection, $credentials)
    {
        $defined = $this->getServerCredentials($connection);
        foreach ($credentials as $key => $value) {
            if (array_get($defined, $key) === true) {
                unset($credentials[$key]);
            }
        }

        return $credentials;
    }

    /**
     * Unify a connection's declaration into the servers form
     *
     * @param array $connection
     *
     * @return array
     */
    protected function unifyMultiserversDeclarations($connection)
    {
        $connection = (array) $connection;
        foreach ($connection as $key => $servers) {
            $servers          = Arr::get($servers, 'servers', [$servers]);
            $connection[$key] = ['servers' => array_values($servers)];
        }

        return $connection;
    }
}
