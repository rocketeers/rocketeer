<?php
namespace Rocketeer\Services\Filesystem;

use League\Flysystem\Adapter\Ftp;
use Rocketeer\Services\Connections\Credentials\Keys\ConnectionKey;

class FtpAdapter extends Ftp
{
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
