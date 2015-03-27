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
 * and their credentials.
 *
 * @author Maxime Fabre <ehtnam6@gmail.com>
 */
class ConnectionsHandler
{
    use HasLocator;

    /**
     * The current handle.
     *
     * @type string
     */
    protected $handle;

    /**
     * The current stage.
     *
     * @type string
     */
    protected $stage;

    /**
     * The current server.
     *
     * @type int
     */
    protected $currentServer = 0;

    /**
     * The connections to use.
     *
     * @type array|null
     */
    protected $connections;

    /**
     * The current connection.
     *
     * @type string|null
     */
    protected $connection;

    /**
     * Build the current connection's handle.
     *
     * @param string|null $connection
     * @param int|null    $server
     * @param string|null $stage
     *
     * @return string
     */
    public function getHandle($connection = null, $server = null, $stage = null)
    {
        // Get identifiers
        $connection = $connection ?: $this->getConnection();
        $server     = $server ?: $this->getServer();
        $stage      = $stage ?: $this->getStage();

        // Filter values
        $handle = [$connection, $server, $stage];
        if ($this->isMultiserver($connection)) {
            $handle = array_filter($handle, function ($value) {
                return !is_null($value);
            });
        } else {
            $handle = array_filter($handle);
        }

        // Concatenate
        $handle       = implode('/', $handle);
        $this->handle = $handle;

        return $handle;
    }

    //////////////////////////////////////////////////////////////////////
    ////////////////////////////// SERVERS ///////////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * @return int
     */
    public function getServer()
    {
        return $this->currentServer;
    }

    /**
     * Check if a connection is multiserver or not.
     *
     * @param string $connection
     *
     * @return bool
     */
    public function isMultiserver($connection)
    {
        return (bool) count($this->getConnectionCredentials($connection));
    }

    ////////////////////////////////////////////////////////////////////
    //////////////////////////////// STAGES ////////////////////////////
    ////////////////////////////////////////////////////////////////////

    /**
     * Get the current stage.
     *
     * @return string
     */
    public function getStage()
    {
        return $this->stage;
    }

    /**
     * Set the stage Tasks will execute on.
     *
     * @param string|null $stage
     */
    public function setStage($stage)
    {
        if ($stage === $this->stage) {
            return;
        }

        $this->stage  = $stage;
        $this->handle = null;

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
    public function getStages()
    {
        return (array) $this->rocketeer->getOption('stages.stages');
    }

    ////////////////////////////////////////////////////////////////////
    ///////////////////////////// APPLICATION //////////////////////////
    ////////////////////////////////////////////////////////////////////

    /**
     * Whether the repository used is using SSH or HTTPS.
     *
     * @return bool
     */
    public function needsCredentials()
    {
        return Str::contains($this->getRepositoryEndpoint(), 'https://');
    }

    /**
     * Get the available connections.
     *
     * @return string[][]|string[]
     */
    public function getAvailableConnections()
    {
        // Fetch stored credentials
        $storage = (array) $this->localStorage->get('connections');

        // Merge with defaults from config file
        $configuration = (array) $this->config->get('rocketeer::connections');

        // Fetch from remote file
        $remote = (array) $this->config->get('remote.connections');

        // Merge configurations
        $connections = array_replace_recursive($remote, $configuration, $storage);

        // Unify multiservers
        foreach ($connections as $key => $servers) {
            $servers           = Arr::get($servers, 'servers', [$servers]);
            $connections[$key] = ['servers' => array_values($servers)];
        }

        return $connections;
    }

    /**
     * Check if a connection has credentials related to it.
     *
     * @param string $connection
     *
     * @return bool
     */
    public function isValidConnection($connection)
    {
        $available = (array) $this->getAvailableConnections();

        return (bool) Arr::get($available, $connection.'.servers');
    }

    /**
     * Get the connection in use.
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
        $instance    = $this;
        $connections = array_filter($connections, function ($value) use ($instance) {
            return $instance->isValidConnection($value);
        });

        // Return default if no active connection(s) set
        if (empty($connections) && $default) {
            return [$default];
        }

        // Set current connection as default
        $this->connections = $connections;

        return $connections;
    }

    /**
     * Set the active connections.
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
        $this->handle      = null;
    }

    /**
     * Get the active connection.
     *
     * @return string
     */
    public function getConnection()
    {
        // Get cached resolved connection
        if ($this->connection) {
            return $this->connection;
        }

        $connection       = Arr::get($this->getConnections(), 0);
        $this->connection = $connection;

        return $this->connection;
    }

    /**
     * Set the current connection.
     *
     * @param string $connection
     * @param int    $server
     */
    public function setConnection($connection, $server = 0)
    {
        if (!$this->isValidConnection($connection) || (($this->connection === $connection) && ($this->currentServer === $server))) {
            return;
        }

        // Set the connection
        $this->handle        = null;
        $this->connection    = $connection;
        $this->localStorage  = $server;
        $this->currentServer = $server;

        // Update events
        $this->tasks->registerConfiguredEvents();
    }

    /**
     * Get the credentials for a particular connection.
     *
     * @param string|null $connection
     *
     * @return string[][]
     */
    public function getConnectionCredentials($connection = null)
    {
        $connection = $connection ?: $this->getConnection();
        $available  = $this->getAvailableConnections();

        return Arr::get($available, $connection.'.servers');
    }

    /**
     * Get thecredentials for as server.
     *
     * @param string|null $connection
     * @param int|null    $server
     *
     * @return mixed
     */
    public function getServerCredentials($connection = null, $server = null)
    {
        $connection = $this->getConnectionCredentials($connection);
        $server     = !is_null($server) ? $server : $this->currentServer;

        return Arr::get($connection, $server, []);
    }

    /**
     * Sync Rocketeer's credentials with Laravel's.
     *
     * @param string|null   $connection
     * @param string[]|null $credentials
     * @param int           $server
     */
    public function syncConnectionCredentials($connection = null, array $credentials = [], $server = 0)
    {
        // Store credentials if any
        if ($credentials) {
            $this->localStorage->set('connections.'.$connection.'.servers.'.$server, $credentials);
        }

        // Get connection
        $connection  = $connection ?: $this->getConnection();
        $credentials = $this->getConnectionCredentials($connection);

        $this->config->set('remote.connections.'.$connection, $credentials);
    }

    /**
     * Flush active connection(s).
     */
    public function disconnect()
    {
        $this->connection  = null;
        $this->connections = null;
    }

    ////////////////////////////////////////////////////////////////////
    /////////////////////////// GIT REPOSITORY /////////////////////////
    ////////////////////////////////////////////////////////////////////

    /**
     * Get the credentials for the repository.
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
     * Get the URL to the Git repository.
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
     * Get the repository branch to use.
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
        exec($this->scm->currentBranch(), $fallback);
        $fallback = Arr::get($fallback, 0, 'master');
        $fallback = trim($fallback);
        $branch   = $this->rocketeer->getOption('scm.branch') ?: $fallback;

        return $branch;
    }
}
