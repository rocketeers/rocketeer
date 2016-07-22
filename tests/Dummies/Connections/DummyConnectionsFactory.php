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

namespace Rocketeer\Dummies\Connections;

use League\Flysystem\AdapterInterface;
use Rocketeer\Services\Connections\Credentials\Keys\ConnectionKey;

class DummyConnectionsFactory
{
    /**
     * @var array
     */
    protected $expectations = [];

    /**
     * @var AdapterInterface
     */
    protected $adapter;

    /**
     * DummyConnectionsFactory constructor.
     *
     * @param array            $expectations
     * @param AdapterInterface $adapter
     */
    public function __construct($expectations, AdapterInterface $adapter)
    {
        $this->expectations = $expectations;
        $this->adapter = $adapter;
    }

    /**
     * @param ConnectionKey $connectionKey
     *
     * @return DummyConnection
     */
    public function make(ConnectionKey $connectionKey)
    {
        $connection = new DummyConnection($connectionKey);
        $connection->setExpectations($this->expectations);
        $connection->setAdapter($this->adapter);

        return $connection;
    }
}
