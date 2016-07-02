<?php

/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Rocketeer\Services\Connections\Connections;

use Closure;
use League\Flysystem\Filesystem;
use League\Flysystem\Sftp\SftpAdapter;
use phpseclib\Net\SFTP;
use Rocketeer\Exceptions\TimeOutException;
use Rocketeer\Interfaces\HasRolesInterface;
use Rocketeer\Services\Connections\Credentials\Keys\ConnectionKey;
use Rocketeer\Traits\Properties\HasRoles;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Represents a connection to a server and stage.
 *
 * @author Maxime Fabre <ehtnam6@gmail.com>
 * @author Taylor Otwell <taylorotwell@gmail.com>
 */
class Connection extends Filesystem implements ConnectionInterface, HasRolesInterface
{
    use HasRoles;

    /**
     * @var SftpAdapter
     */
    protected $adapter;

    /**
     * The connection key.
     *
     * @var ConnectionKey
     */
    protected $connectionKey;

    /**
     * The output implementation for the connection.
     *
     * @var OutputInterface
     */
    protected $output;

    /**
     * Create a new SSH connection instance.
     *
     * @param ConnectionKey $connectionKey
     */
    public function __construct(ConnectionKey $connectionKey)
    {
        $this->connectionKey = $connectionKey;
        $this->roles = (array) $connectionKey->roles;

        parent::__construct(new SftpAdapter([
            'host' => $connectionKey->host,
            'username' => $connectionKey->username,
            'password' => $connectionKey->password,
            'privateKey' => $connectionKey->key,
            'root' => $connectionKey->root_directory,
        ]));
    }

    ////////////////////////////////////////////////////////////////////////////////
    ///////////////////////////// GETTERS AND SETTERS //////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////

    /**
     * Get the SFTP connection.
     *
     * @return SFTP
     */
    public function getGateway()
    {
        return $this->adapter->getConnection();
    }

    /**
     * @return ConnectionKey
     */
    public function getConnectionKey()
    {
        return $this->connectionKey;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->connectionKey->name;
    }

    /**
     * @return string|null
     */
    public function getUsername()
    {
        return $this->connectionKey->username;
    }

    ////////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////// EXECUTION ///////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////

    /**
     * Run a set of commands against the connection.
     *
     * @param string|array $commands
     * @param Closure|null $callback
     */
    public function run($commands, Closure $callback = null)
    {
        $gateway = $this->getGateway();
        $commands = is_array($commands) ? implode(' && ', $commands) : $commands;

        $gateway->exec($commands, $callback);
        if ($gateway->isTimeout()) {
            $message = sprintf('Connection timeout of %ds exceeded', $gateway->timeout);
            throw new TimeOutException($message);
        }
    }

    /**
     * Get the exit status of the last command.
     *
     * @return int|bool
     */
    public function status()
    {
        return $this->getGateway()->getExitStatus();
    }
}
