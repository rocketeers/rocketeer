<?php

/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Rocketeer\Traits\Exceptions;

use Rocketeer\Services\Connections\Credentials\Keys\ConnectionKey;

trait WithCredentials
{
    /**
     * Set the credentials on an Exception.
     *
     * @param ConnectionKey $connectionKey
     */
    public function setCredentials(ConnectionKey $connectionKey)
    {
        $this->message .= PHP_EOL.'With credentials:'.PHP_EOL.json_encode($connectionKey->toArray(), JSON_PRETTY_PRINT);
    }
}
