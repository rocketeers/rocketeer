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

use League\Flysystem\Filesystem;
use League\Flysystem\Sftp\SftpAdapter;
use Rocketeer\Exceptions\MissingCredentialsException;
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
        $credentials = $connectionKey->getServerCredentials();
        $handle = (string) $connectionKey->toHandle();

        // Check the cache for already resolved connection
        if (isset($this->connected[$handle])) {
            return $this->connected[$handle];
        }

        if (!isset($credentials['host'])) {
            throw new MissingCredentialsException('Host is required for '.$connectionKey->name);
        }

        // Create connection
        $connection = new Connection(
            $connectionKey,
            $this->getAuth($credentials, $connectionKey)
        );

        // Set filesystem on connection
        $filesystem = new Filesystem(new SftpAdapter([
            'host' => $connectionKey->host,
            'username' => $connectionKey->username,
            'password' => $connectionKey->password,
            'privateKey' => $connectionKey->key,
            'root' => $connectionKey->root_directory,
        ]));

        $connection->setFilesystem($filesystem);

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

    /**
     * Format the appropriate authentication array payload.*.
     *
     * @param array         $config
     * @param ConnectionKey $connectionKey
     *
     * @return array
     */
    protected function getAuth(array $config, ConnectionKey $connectionKey)
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

        $exception = new MissingCredentialsException('Password / key is required.');
        $exception->setCredentials($connectionKey);

        throw $exception;
    }
}
