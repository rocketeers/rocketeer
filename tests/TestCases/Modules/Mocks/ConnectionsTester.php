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

use Rocketeer\Dummies\Connections\DummyConnectionsFactory;
use Rocketeer\Services\Connections\ConnectionsFactory;

trait ConnectionsTester
{
    /**
     * @param string|array|null $expectations
     */
    protected function bindDummyConnection($expectations = null)
    {
        $factory = new DummyConnectionsFactory($expectations, $this->files->getAdapter());
        $this->container->add(ConnectionsFactory::class, $factory);

        $this->connections->disconnect();
    }
}
