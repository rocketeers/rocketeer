<?php

/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Rocketeer\Services\Filesystem;

use League\Flysystem\Sftp\SftpAdapter;
use Rocketeer\Services\Credentials\Keys\ConnectionKey;

class ConnectionKeyAdapter extends SftpAdapter
{
    /**
     * @param ConnectionKey $connectionKey
     * @param string        $root
     */
    public function __construct(ConnectionKey $connectionKey, $root)
    {
        parent::__construct([
            'host'       => $connectionKey->host,
            'username'   => $connectionKey->username,
            'password'   => $connectionKey->password,
            'privateKey' => $connectionKey->key,
            'root'       => $root,
        ]);
    }
}
