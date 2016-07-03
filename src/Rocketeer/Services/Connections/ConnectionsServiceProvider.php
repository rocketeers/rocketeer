<?php

/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Rocketeer\Services\Connections;

use League\Container\ServiceProvider\AbstractServiceProvider;
use Rocketeer\Bash;
use Rocketeer\Services\Connections\Connections\LocalConnection;
use Rocketeer\Services\Connections\Credentials\CredentialsGatherer;
use Rocketeer\Services\Connections\Credentials\CredentialsHandler;

class ConnectionsServiceProvider extends AbstractServiceProvider
{
    /**
     * @var array
     */
    protected $provides = [
        Bash::class,
        ConnectionsFactory::class,
        'remote.local',
        'connections',
        'coordinator',
        'credentials.handler',
        'credentials.gatherer',
    ];

    /**
     * {@inheritdoc}
     */
    public function register()
    {
        $this->container->share(Bash::class)->withArgument($this->container);
        $this->container->share(ConnectionsFactory::class);
        $this->container->share('remote.local', LocalConnection::class)->withArgument($this->container);
        $this->container->share('connections', ConnectionsHandler::class)->withArgument($this->container);
        $this->container->share('coordinator', Coordinator::class)->withArgument($this->container);
        $this->container->share('credentials.handler', CredentialsHandler::class)->withArgument($this->container);
        $this->container->share('credentials.gatherer', CredentialsGatherer::class)->withArgument($this->container);
    }
}
