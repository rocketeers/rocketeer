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

namespace Rocketeer\TestCases\Modules\Mocks;

use Prophecy\Argument;
use Rocketeer\Dummies\Connections\DummyConnection;
use Rocketeer\Services\Connections\ConnectionsFactory;
use Rocketeer\Services\Connections\Credentials\Keys\ConnectionKey;

trait Connections
{
    /**
     * Mock the RemoteHandler.
     *
     * @param string|array|null $expectations
     */
    protected function mockRemote($expectations = null)
    {
        $this->container->add(ConnectionsFactory::class, $this->getConnectionsFactory($expectations));
        $this->connections->disconnect();
    }

    /**
     * @param string|array $expectations
     *
     * @return ConnectionsFactory
     */
    protected function getConnectionsFactory($expectations = null)
    {
        $me = $this;

        /** @var ConnectionsFactory $factory */
        $factory = $this->prophesize(ConnectionsFactory::class);
        $factory->make(Argument::type(ConnectionKey::class))->will(function ($arguments) use ($me, $expectations) {
            $connection = new DummyConnection($arguments[0]);
            $connection->setExpectations($expectations);
            if ($adapter = $me->files->getAdapter()) {
                $connection->setAdapter($adapter);
            }

            return $connection;
        });

        return $factory->reveal();
    }
}
