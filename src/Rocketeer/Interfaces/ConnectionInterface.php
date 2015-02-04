<?php
namespace Rocketeer\Interfaces;

use Closure;

interface ConnectionInterface
{
    /**
     * Run a set of commands against the connection.
     *
     * @param string|array $commands
     * @param Closure      $callback
     *
     * @return void
     */
    public function run($commands, Closure $callback = null);

    /**
     * Upload a local file to the server.
     *
     * @param string $local
     * @param string $remote
     *
     * @return void
     */
    public function put($local, $remote);

    /**
     * Upload a string to to the given file on the server.
     *
     * @param string $remote
     * @param string $contents
     *
     * @return void
     */
    public function putString($remote, $contents);

    /**
     * Get the exit status of the last command.
     *
     * @return integer|bool
     */
    public function status();
}
