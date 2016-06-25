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
use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use Rocketeer\Interfaces\ConnectionInterface;
use Rocketeer\Interfaces\GatewayInterface;
use Rocketeer\Interfaces\HasRolesInterface;
use Rocketeer\Services\Connections\Gateways\SeclibGateway;
use Rocketeer\Services\Credentials\Keys\ConnectionKey;
use Rocketeer\Traits\Properties\HasRoles;
use RuntimeException;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Base connection class with additional setters.
 *
 * @author Maxime Fabre <ehtnam6@gmail.com>
 * @author Taylor Otwell <taylorotwell@gmail.com>
 */
class Connection implements ConnectionInterface, HasRolesInterface
{
    use HasRoles;

    /**
     * The SSH gateway implementation.
     *
     * @var GatewayInterface
     */
    protected $gateway;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * The connection handle.
     *
     * @var ConnectionKey
     */
    protected $handle;

    /**
     * The output implementation for the connection.
     *
     * @var OutputInterface
     */
    protected $output;

    /**
     * Create a new SSH connection instance.
     *
     * @param ConnectionKey         $handle
     * @param array                 $auth
     * @param GatewayInterface|null $gateway
     */
    public function __construct(ConnectionKey $handle, array $auth, GatewayInterface $gateway = null)
    {
        $this->handle = $handle;
        $this->gateway = $gateway ?: new SeclibGateway($handle->host, $auth, new Filesystem(new Local('/')));
        $this->roles = $handle->roles;
    }

    /**
     * @param string $name
     * @param array  $arguments
     *
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        return call_user_func_array([$this->filesystem, $name], $arguments);
    }

    /**
     * Run a set of commands against the connection.
     *
     * @param string|array $commands
     * @param Closure|null $callback
     */
    public function run($commands, Closure $callback = null)
    {
        $commands = $this->formatCommands($commands);

        $this->getGateway()->run($commands, $callback);
    }

    /**
     * Download the contents of a remote file.
     *
     * @param string $remote
     * @param string $local
     */
    public function get($remote, $local)
    {
        $this->getGateway()->get($remote, $local);
    }

    /**
     * Get the contents of a remote file.
     *
     * @param string $remote
     *
     * @return string
     */
    public function getString($remote)
    {
        return $this->getGateway()->getString($remote);
    }

    /**
     * Upload a local file to the server.
     *
     * @param string $local
     * @param string $remote
     */
    public function put($local, $remote)
    {
        $this->getGateway()->put($local, $remote);
    }

    /**
     * Upload a string to to the given file on the server.
     *
     * @param string $remote
     * @param string $contents
     */
    public function putString($remote, $contents)
    {
        $this->getGateway()->putString($remote, $contents);
    }

    /**
     * Format the given command set.
     *
     * @param string|array $commands
     *
     * @return string
     */
    protected function formatCommands($commands)
    {
        return is_array($commands) ? implode(' && ', $commands) : $commands;
    }

    /**
     * Get the exit status of the last command.
     *
     * @return int|bool
     */
    public function status()
    {
        return $this->gateway->status();
    }

    /**
     * Get the gateway implementation.
     *
     * @throws RuntimeException
     *
     * @return GatewayInterface
     */
    public function getGateway()
    {
        if (!$this->gateway->connected() && !$this->gateway->connect($this->getUsername())) {
            throw new RuntimeException('Unable to connect to remote server.');
        }

        return $this->gateway;
    }

    /**
     * Get the output implementation for the connection.
     *
     * @return OutputInterface
     */
    public function getOutput()
    {
        if ($this->output === null) {
            $this->output = new NullOutput();
        }

        return $this->output;
    }

    /**
     * Set the output implementation.
     *
     * @param OutputInterface $output
     */
    public function setOutput(OutputInterface $output)
    {
        $this->output = $output;
    }

    /**
     * @return Filesystem
     */
    public function getFilesystem()
    {
        return $this->filesystem;
    }

    /**
     * @param Filesystem $filesystem
     */
    public function setFilesystem($filesystem)
    {
        $this->filesystem = $filesystem;
    }

    /**
     * @return ConnectionKey
     */
    public function getHandle()
    {
        return $this->handle;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->handle->name;
    }

    /**
     * @return string|null
     */
    public function getUsername()
    {
        return $this->handle->username;
    }
}
