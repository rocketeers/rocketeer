<?php

/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace Rocketeer\Dummies\Connections;

use Closure;
use Rocketeer\Services\Connections\Connections\LocalConnection;

class DummyConnection extends LocalConnection
{
    /**
     * @var string
     */
    protected $expectations;

    /**
     * @var bool
     */
    protected $connected;

    /**
     * @var int
     */
    protected $previousStatus = 0;

    /**
     * @param bool $connected
     */
    public function setConnected($connected)
    {
        $this->connected = $connected;
    }

    /**
     * @return bool
     */
    public function isConnected()
    {
        return $this->connected;
    }

    /**
     * @param int $previousStatus
     */
    public function setPreviousStatus($previousStatus)
    {
        $this->previousStatus = $previousStatus;
    }

    /**
     * @param string $expectations
     */
    public function setExpectations($expectations)
    {
        $this->expectations = $expectations;
    }

    /**
     * {@inheritdoc}
     */
    public function run($commands, Closure $callback = null)
    {
        // Check in predefined expectations
        if (is_string($commands) || count($commands) === 1) {
            $command = is_array($commands) ? $commands[0] : $commands;
            if ($command === "bash --login -c 'echo ROCKETEER'") {
                return $callback('Inappropriate ioctl for device'.PHP_EOL.'ROCKETEER');
            } elseif (is_string($this->expectations)) {
                return $callback($this->expectations);
            } elseif (isset($this->expectations[$command])) {
                return $callback($this->expectations[$command]);
            }
        }

        return parent::run($commands, $callback);
    }
}
