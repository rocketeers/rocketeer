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

trait WithCredentials
{
    /**
     * Set the credentials that failed to connect
     *
     * @param array $credentials
     */
    public function setCredentials(array $credentials)
    {
        $this->message .= PHP_EOL.'With credentials:'.PHP_EOL.json_encode($credentials, JSON_PRETTY_PRINT);
    }
}
