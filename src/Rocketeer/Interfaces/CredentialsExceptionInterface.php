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

use Rocketeer\Services\Connections\Credentials\Keys\ConnectionKey;

/**
 * An exception that can dump credentials.
 *
 * @author Maxime Fabre <ehtnam6@gmail.com>
 */
interface CredentialsExceptionInterface
{
    /**
     * Set the credentials that failed to connect.
     *
     * @param ConnectionKey $connectionKey
     */
    public function setCredentials(ConnectionKey $connectionKey);
}
