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

use Closure;
use Illuminate\Filesystem\Filesystem;
use Rocketeer\Interfaces\ConnectionInterface;
use Rocketeer\Interfaces\GatewayInterface;
use Rocketeer\Interfaces\HasRolesInterface;
use Rocketeer\Services\Connections\Gateways\SeclibGateway;
use Rocketeer\Traits\Properties\HasRoles;
use RuntimeException;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Base connection class with additional setters
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
     * The name of the connection.
     *
     * @var string
     */
    protected $name;

    /**
     * The host name of the server.
     *
     * @var string
     */
    protected $host;

    /**
     * The username for the connection.
     *
     * @var string
     */
    protected $username;

    /**
     * All of the defined tasks.
     *
     * @var array
     */
    protected $tasks = array();

    /**
     * The output implementation for the connection.
     *
     * @var \Symfony\Component\Console\Output\OutputInterface
     */
    protected $output;

    /**
     * Create a new SSH connection instance.
     *
     * @param  string  $name
     * @param  string  $host
     * @param  string  $username
     * @param  array   $auth
     * @param  GatewayInterface $gateway
     */
    public function __construct($name, $host, $username, array $auth, GatewayInterface $gateway = null)
    {
        $this->name = $name;
        $this->host = $host;
        $this->username = $username;
        $this->gateway = $gateway ?: new SeclibGateway($host, $auth, new Filesystem());
    }

    /**
     * Run a set of commands against the connection.
     *
     * @param  string|array  $commands
     * @param  Closure  $callback
     * @return void
     */
    public function run($commands, Closure $callback = null)
    {
        // First, we will initialize the SSH gateway, and then format the commands so
        // they can be run. Once we have the commands formatted and the server is
        // ready to go we will just fire off these commands against the server.
        $gateway = $this->getGateway();

        $callback = $this->getCallback($callback);

        $gateway->run($this->formatCommands($commands));

        // After running the commands against the server, we will continue to ask for
        // the next line of output that is available, and write it them out using
        // our callback. Once we hit the end of output, we'll bail out of here.
        while (true)
        {
            if (is_null($line = $gateway->nextLine())) break;

            call_user_func($callback, $line, $this);
        }
    }

    /**
     * Download the contents of a remote file.
     *
     * @param  string  $remote
     * @param  string  $local
     * @return void
     */
    public function get($remote, $local)
    {
        $this->getGateway()->get($remote, $local);
    }

    /**
     * Get the contents of a remote file.
     *
     * @param  string  $remote
     * @return string
     */
    public function getString($remote)
    {
        return $this->getGateway()->getString($remote);
    }

    /**
     * Upload a local file to the server.
     *
     * @param  string  $local
     * @param  string  $remote
     * @return void
     */
    public function put($local, $remote)
    {
        $this->getGateway()->put($local, $remote);
    }

    /**
     * Upload a string to to the given file on the server.
     *
     * @param  string  $remote
     * @param  string  $contents
     * @return void
     */
    public function putString($remote, $contents)
    {
        $this->getGateway()->putString($remote, $contents);
    }

    /**
     * Display the given line using the default output.
     *
     * @param  string  $line
     * @return void
     */
    public function display($line)
    {
        $server = $this->username.'@'.$this->host;

        $lead = '<comment>['.$server.']</comment> <info>('.$this->name.')</info>';

        $this->getOutput()->writeln($lead.' '.$line);
    }

    /**
     * Format the given command set.
     *
     * @param  string|array  $commands
     * @return string
     */
    protected function formatCommands($commands)
    {
        return is_array($commands) ? implode(' && ', $commands) : $commands;
    }

    /**
     * Get the display callback for the connection.
     *
     * @param  Closure|null  $callback
     * @return Closure
     */
    protected function getCallback($callback)
    {
        if ( ! is_null($callback)) return $callback;

        return function($line) { $this->display($line); };
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
     * @return GatewayInterface
     *
     * @throws RuntimeException
     */
    public function getGateway()
    {
        if ( ! $this->gateway->connected() && ! $this->gateway->connect($this->username))
        {
            throw new RuntimeException("Unable to connect to remote server.");
        }

        return $this->gateway;
    }

    /**
     * Get the output implementation for the connection.
     *
     * @return \Symfony\Component\Console\Output\OutputInterface
     */
    public function getOutput()
    {
        if (is_null($this->output)) $this->output = new NullOutput();

        return $this->output;
    }

    /**
     * Set the output implementation.
     *
     * @param  \Symfony\Component\Console\Output\OutputInterface  $output
     * @return void
     */
    public function setOutput(OutputInterface $output)
    {
        $this->output = $output;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }
}
