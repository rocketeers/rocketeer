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

use League\Flysystem\Sftp\SftpAdapter;
use phpseclib\System\SSH\Agent;
use Rocketeer\Services\Connections\Credentials\Keys\ConnectionKey;

class ConnectionKeyAdapter extends SftpAdapter
{
    /**
     * @var bool
     */
    protected $agent;

    /**
     * @param ConnectionKey $connectionKey
     */
    public function __construct(ConnectionKey $connectionKey)
    {
        $this->configurable[] = 'agent';

        parent::__construct([
            'host' => $connectionKey->host,
            'username' => $connectionKey->username,
            'password' => $connectionKey->password,
            'privateKey' => $connectionKey->key,
            'agent' => (bool) $connectionKey->agent,
            'root' => '/',
            'timeout' => 60 * 60,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    protected function login()
    {
        parent::login();

        $authentication = $this->getPassword();
        if ($authentication instanceof Agent) {
            $authentication->startSSHForwarding($this->connection);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getPassword()
    {
        if ($this->agent) {
            return $this->getAgent();
        }

        return parent::getPassword();
    }

    /**
     * @return Agent|bool
     */
    public function getAgent()
    {
        if ($this->agent === true) {
            $this->agent = new Agent();
        }

        return $this->agent;
    }

    /**
     * @param bool $agent
     *
     * @return $this
     */
    public function setAgent($agent)
    {
        $this->agent = (bool) $agent;

        return $this;
    }
}
