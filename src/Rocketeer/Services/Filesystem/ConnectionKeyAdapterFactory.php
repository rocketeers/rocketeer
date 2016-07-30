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
use League\Flysystem\AdapterInterface;
use League\Flysystem\Sftp\SftpAdapter;
use Rocketeer\Services\Connections\Credentials\Keys\ConnectionKey;

/**
 * Creates the correct Flysystem adapter according
 * to a ConnectionKey instance.
 */
class ConnectionKeyAdapterFactory
{
    /**
     * @param ConnectionKey $connectionKey
     *
     * @return AdapterInterface
     */
    public function getAdapter(ConnectionKey $connectionKey)
    {
        $adapter = $connectionKey->isFtp() ? Ftp::class : SftpAdapter::class;

        return new $adapter([
            'host' => $connectionKey->host,
            'username' => $connectionKey->username,
            'password' => $connectionKey->password,
            'privateKey' => $connectionKey->key,
            'use_agent' => (bool) $connectionKey->agent,
            'root' => '/',
            'timeout' => 60 * 60,
        ]);
    }
}
