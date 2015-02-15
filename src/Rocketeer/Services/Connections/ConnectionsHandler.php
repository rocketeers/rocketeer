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
use Rocketeer\Services\Credentials\Keychains\ConnectionKeychain;
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

    /**
     * Flush active connection(s)
     */
    public function disconnect()
    {
        $this->current     = null;
        $this->connections = null;
    }

    ////////////////////////////////////////////////////////////////////
    ///////////////////////////// REPOSITORY ///////////////////////////
    ////////////////////////////////////////////////////////////////////

    /**
     * Whether the repository used is using SSH or HTTPS
     *
     * @return boolean
     */
    public function repositoryNeedsCredentials()
    {
        return Str::contains($this->getRepositoryEndpoint(), 'https://');
    }

    /**
     * Get the credentials for the repository
     *
     * @return array
     */
    public function getRepositoryCredentials()
    {
        $config      = (array) $this->rocketeer->getOption('scm');
        $credentials = (array) $this->localStorage->get('credentials');

        return array_merge($config, $credentials);
    }

    /**
     * Get the URL to the Git repository
     *
     * @return string
     */
    public function getRepositoryEndpoint()
    {
        // Get credentials
        $repository = $this->getRepositoryCredentials();
        $username   = Arr::get($repository, 'username');
        $password   = Arr::get($repository, 'password');
        $repository = Arr::get($repository, 'repository');

        // Add credentials if possible
        if ($username || $password) {

            // Build credentials chain
            $credentials = $password ? $username.':'.$password : $username;
            $credentials .= '@';

            // Add them in chain
            $repository = preg_replace('#https://(.+)@#', 'https://', $repository);
            $repository = str_replace('https://', 'https://'.$credentials, $repository);
        }

        return $repository;
    }

    /**
     * Get the repository branch to use
     *
     * @return string
     */
    public function getRepositoryBranch()
    {
        // If we passed a branch, use it
        if ($branch = $this->getOption('branch')) {
            return $branch;
        }

        // Compute the fallback branch
        $fallback = $this->bash->onLocal(function () {
            return $this->scm->runSilently('currentBranch');
        });
        $fallback = $fallback ?: 'master';
        $fallback = trim($fallback);
        $branch   = $this->rocketeer->getOption('scm.branch') ?: $fallback;

        return $branch;
    }

    /**
     * Get repository name to use
     *
     * @return string
     */
    public function getRepositoryName()
    {
        $repository = $this->getRepositoryEndpoint();
        $repository = preg_replace('#https?://(.+)\.com/(.+)/([^.]+)(\..+)?#', '$2/$3', $repository);

        return $repository;
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
