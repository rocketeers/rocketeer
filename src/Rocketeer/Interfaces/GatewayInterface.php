<?php
/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Rocketeer\Interfaces;

interface GatewayInterface
{
    /**
     * Connect to the SSH server.
     *
     * @param string $username
     */
    public function connect($username);

    /**
     * Determine if the gateway is connected.
     *
     * @return bool
     */
    public function connected();

    /**
     * Run a command against the server (non-blocking).
     *
     * @param string $command
     */
    public function run($command);

    /**
     * Upload a local file to the server.
     *
     * @param string $local
     * @param string $remote
     */
    public function put($local, $remote);

    /**
     * Upload a string to to the given file on the server.
     *
     * @param string $remote
     * @param string $contents
     */
    public function putString($remote, $contents);

    /**
     * Get the next line of output from the server.
     *
     * @return string|null
     */
    public function nextLine();

    /**
     * Get the exit status of the last command.
     *
     * @return int|bool
     */
    public function status();
}
