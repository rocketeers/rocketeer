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

namespace Rocketeer\Services\Filesystem;

use League\Flysystem\Adapter\Ftp;
use Rocketeer\Services\Connections\Credentials\Keys\ConnectionKey;

class FtpAdapter extends Ftp
{
    /**
     * @param ConnectionKey $connectionKey
     */
    public function __construct(ConnectionKey $connectionKey)
    {
        parent::__construct([
            'host' => $connectionKey->host,
            'username' => $connectionKey->username,
            'password' => $connectionKey->password,
            'timeout' => $connectionKey->timeout,
            'root' => '/',
            'timeout' => 60 * 60,
        ]);
    }
}
