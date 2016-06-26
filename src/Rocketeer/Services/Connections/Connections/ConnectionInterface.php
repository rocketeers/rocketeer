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

interface ConnectionInterface
{
    /**
     * Run a set of commands against the connection.
     *
     * @param string|array $commands
     * @param Closure|null $callback
     */
    public function run($commands, Closure $callback = null);

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
     * Get the exit status of the last command.
     *
     * @return int|bool
     */
    public function status();
}
