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
     * @type ConnectionHandle
     */
    protected $current;

    /**
     * The connections to use
     *
     * @type array|null
     */
    protected $connections;

    /**
     * Build the current connection's handle
     *
     * @param ConnectionHandle|string|null $connection
     * @param integer|null                 $server
     * @param string|null                  $stage
     *
     * @return ConnectionHandle
     */
    public function getHandle($connection = null, $server = null, $stage = null)
    {
        if ($connection instanceof ConnectionHandle) {
            return $connection;
        }

        // Get identifiers
        $connection = $connection ?: Arr::get($this->getConnections(), 0);
        $server     = $server ?: 0;
        $stage      = $stage ?: null;

        // Concatenate
        $handle = new ConnectionHandle($connection, $server, $stage, $this->getCurrentUsername());

        // Replace server index by hostname
        $handle->multiserver = $this->isMultiserver($handle);
        $handle->server      = array_get($this->getServerCredentials($handle), 'host', $server);

        return $handle;
    }

    /**
     * Get the long form of the handle
     *
     * @return string
     */
    public function getLongHandle()
    {
        return $this->getCurrent()->toLongHandle();
    }

    /**
     * Get the currently authenticated user
     *
     * @return string
     */
    public function getCurrentUsername()
    {
        return $this->remote->connected() ? $this->remote->getUsername() : null;
    }

    //////////////////////////////////////////////////////////////////////
    ////////////////////////////// SERVERS ///////////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * @return int
     */
    public function getServer()
    {
        return $this->current->server;
    }

    /**
     * Check if a connection is multiserver or not
     *
     * @param ConnectionHandle $connection
     *
     * @return boolean
     */
    public function isMultiserver(ConnectionHandle $connection)
    {
        return count($this->getConnectionCredentials($connection)) > 1;
    }

    ////////////////////////////////////////////////////////////////////
    //////////////////////////////// STAGES ////////////////////////////
    ////////////////////////////////////////////////////////////////////

    /**
     * Get the current stage
     *
     * @return string
     */
    public function getStage()
    {
        return $this->getCurrent()->stage;
    }

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
    public function getStages()
    {
        return (array) $this->rocketeer->getOption('stages.stages');
    }

    ////////////////////////////////////////////////////////////////////
    ///////////////////////////// APPLICATION //////////////////////////
    ////////////////////////////////////////////////////////////////////

    /**
     * Whether the repository used is using SSH or HTTPS
     *
     * @return boolean
     */
    public function needsCredentials()
    {
        return Str::contains($this->getRepositoryEndpoint(), 'https://');
    }

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

        // Fetch from remote file
        $remote = $this->config->get('remote.connections');
        $remote = $this->unifyMultiserversDeclarations($remote);

        // Merge configurations
        $connections = array_replace_recursive($remote, $configuration, $storage);

        return $connections;
    }

    /**
     * Check if a connection has credentials related to it
     *
     * @param ConnectionHandle|string $connection
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

        // Get all and defaults
        $connections = (array) $this->config->get('rocketeer::default');
        $default     = $this->config->get('remote.default');

        // Remove invalid connections
        $connections = array_filter($connections, [$this, 'isValidConnection']);

        // Return default if no active connection(s) set
        if (empty($connections) && $default) {
            return array($default);
        }

        // Set current connection as default
        $this->connections = $connections;

        return $connections;
    }

    /**
     * @return string
     */
    public function getConnection()
    {
        return $this->getCurrent()->name;
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
     * @return ConnectionHandle
     */
    public function getCurrent()
    {
        // Return local handle
        if ($this->rocketeer->isLocal()) {
            $handle = new ConnectionHandle('local', null, null, $this->getCurrentUsername());
        } elseif ($this->current && $this->current->name) {
            $handle = $this->current;
        } else {
            $this->current = $handle = $this->getHandle();
        }

        return $handle;
    }

    /**
     * Set the current connection
     *
     * @param ConnectionHandle|string $connection
     * @param integer                 $server
     */
    public function setConnection($connection, $server = 0)
    {
        $connection = $connection instanceof ConnectionHandle ? $connection : $this->getHandle($connection, $server);
        if (!$this->isValidConnection($connection) || ($this->getCurrent()->is($connection->name))) {
            return;
        }

        // Set the connection
        $this->current      = $connection;
        $this->localStorage = $connection->server;

        // Update events
        $this->tasks->registerConfiguredEvents();
    }

    /**
     * Get the credentials for a particular connection
     *
     * @param ConnectionHandle|null $connection
     *
     * @return string[][]
     */
    public function getConnectionCredentials(ConnectionHandle $connection = null)
    {
        $connection = $connection ?: $this->getCurrent();
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
     * Get thecredentials for as server
     *
     * @param ConnectionHandle|null $connection
     *
     * @return mixed
     */
    public function getServerCredentials(ConnectionHandle $connection = null)
    {
        $servers = $this->getConnectionCredentials($connection);
        $server  = $connection && $connection->server !== null ? $connection->server : $this->current->server;

        return Arr::get($servers, $server);
    }

    /**
     * Sync Rocketeer's credentials with Laravel's
     *
     * @param ConnectionHandle|null $connection
     * @param array                 $credentials
     */
    public function syncConnectionCredentials(ConnectionHandle $connection = null, array $credentials = [])
    {
        // Store credentials if any
        if ($credentials) {
            $filtered = $this->filterUnsavableCredentials($connection, $credentials);
            $this->localStorage->set('connections.'.$connection.'.servers.'.$connection->server, $filtered);

            $this->config->set('rocketeer::connections.'.$connection->toHandle(), $credentials);
        }

        // Get connection
        $connection  = $connection ?: $this->getCurrent();
        $credentials = $credentials ?: $this->getConnectionCredentials($connection);

        $this->config->set('remote.connections.'.$connection->name, $credentials);
    }

    /**
     * Filter the credentials and remove the ones that
     * can't be saved to disk
     *
     * @param ConnectionHandle $connection
     * @param array            $credentials
     *
     * @return string[]
     */
    protected function filterUnsavableCredentials(ConnectionHandle $connection, $credentials)
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
     * Flush active connection(s)
     */
    public function disconnect()
    {
        $this->current     = null;
        $this->connections = null;
    }

    ////////////////////////////////////////////////////////////////////
    /////////////////////////// GIT REPOSITORY /////////////////////////
    ////////////////////////////////////////////////////////////////////

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

    /**
     * @param ConnectionHandle|string $connection
     *
     * @return ConnectionHandle
     */
    protected function sanitizeConnection($connection)
    {
        return $connection instanceof ConnectionHandle ? $connection : $this->getHandle($connection);
    }
}
