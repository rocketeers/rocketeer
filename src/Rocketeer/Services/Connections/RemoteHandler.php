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

use Exception;
use InvalidArgumentException;
use Rocketeer\Exceptions\ConnectionException;
use Rocketeer\Exceptions\MissingCredentialsException;
use Rocketeer\Traits\HasLocator;
use Symfony\Component\Console\Output\NullOutput;

/**
 * Handle creationg and caching of connections.
 *
 * @author Maxime Fabre <ehtnam6@gmail.com>
 * @author Taylor Otwell
 */
class RemoteHandler
{
    use HasLocator;

    /**
     * A storage of active connections.
     *
     * @type Connection[]
     */
    protected $active = [];

    /**
     * Whether the handler is currently connected to any server.
     *
     * @return bool
     */
    public function connected()
    {
        return (bool) $this->active;
    }

    /**
     * Purge all cached connections.
     */
    public function disconnect()
    {
        $this->active = [];
    }

    /**
     * Create a specific connection or the default one.
     *
     * @param string|null $connection
     * @param int         $server
     *
     * @return Connection
     */
    public function connection($connection = null, $server = 0)
    {
        $name   = $connection ?: $this->connections->getConnection();
        $server = $server ?: $this->connections->getServer();
        $handle = $this->connections->getHandle($name, $server);

        // Check the cache
        if (isset($this->active[$handle])) {
            return $this->active[$handle];
        }

        // Create connection
        $credentials = $this->connections->getServerCredentials($connection, $server);
        $connection  = $this->makeConnection($name, $credentials);

        // Save to cache
        $this->active[$handle] = $connection;

        return $connection;
    }

    /**
     * @param string $name
     * @param array  $credentials
     *
     * @throws MissingCredentialsException
     *
     * @return Connection
     */
    protected function makeConnection($name, array $credentials)
    {
        if (!isset($credentials['host']) || !isset($credentials['username'])) {
            throw new MissingCredentialsException('Host and/or username is required for '.$name);
        }

        $connection = new Connection(
            $name,
            $credentials['host'],
            $credentials['username'],
            $this->getAuth($credentials)
        );

        // Set output on connection
        $output = $this->hasCommand() ? $this->command->getOutput() : new NullOutput();
        $connection->setOutput($output);

        return $connection;
    }

    /**
     * Format the appropriate authentication array payload.
     *
     * @param array $config
     *
     * @throws InvalidArgumentException
     *
     * @return array
     */
    protected function getAuth(array $config)
    {
        if (isset($config['agent']) && $config['agent'] === true) {
            return ['agent' => true];
        } elseif (isset($config['key']) && trim($config['key']) !== '') {
            return ['key' => $config['key'], 'keyphrase' => $config['keyphrase']];
        } elseif (isset($config['keytext']) && trim($config['keytext']) !== '') {
            return ['keytext' => $config['keytext']];
        } elseif (isset($config['password'])) {
            return ['password' => $config['password']];
        }

        throw new MissingCredentialsException('Password / key is required.');
    }

    /**
     * Dynamically pass methods to the default connection.
     *
     * @param string $method
     * @param array  $parameters
     *
     * @throws \Rocketeer\Exceptions\ConnectionException
     *
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        try {
            return call_user_func_array([$this->connection(), $method], $parameters);
        } catch (Exception $exception) {
            $exception = new ConnectionException($exception->getMessage());
            $exception->setCredentials($this->connections->getServerCredentials());

            throw $exception;
        }
    }
}
