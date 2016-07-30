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

namespace Rocketeer\Services\Connections;

/**
 * Exception for when a connection times out.
 */
class TimeOutException extends ConnectionException
{
    /**
     * @param int $timeout
     */
    public function __construct($timeout)
    {
        parent::__construct(sprintf('Connection timeout of %ds exceeded', $timeout));
    }
}
