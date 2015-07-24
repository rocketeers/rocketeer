<?php

/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Rocketeer\Exceptions;

use Exception;

/**
 * Exception when Rocketeer can't connect to a server.
 *
 * @author Maxime Fabre <ehtnam6@gmail.com>
 */
class ConnectionException extends Exception
{
    /**
     * Set the credentials that failed to connect.
     *
     * @param array $credentials
     */
    public function setCredentials(array $credentials)
    {
        $this->message .= PHP_EOL.'With credentials:'.PHP_EOL.json_encode($credentials);
    }
}
