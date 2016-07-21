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

namespace Rocketeer\Services\Connections;

use League\Container\ServiceProvider\AbstractServiceProvider;
use Rocketeer\Services\Connections\Connections\LocalConnection;
use Rocketeer\Services\Connections\Credentials\CredentialsGatherer;
use Rocketeer\Services\Connections\Credentials\CredentialsHandler;
use Rocketeer\Services\Connections\Credentials\Modules\ConnectionsKeychain;
use Rocketeer\Services\Connections\Credentials\Modules\RepositoriesKeychain;
use Rocketeer\Services\Connections\Shell\Bash;
use Rocketeer\Traits\HasLocatorTrait;

class ConnectionsServiceProvider extends AbstractServiceProvider
{
    use HasLocatorTrait;

    /**
     * @var array
     */
    protected $provides = [
        'remote.local',
        Bash::class,
        ConnectionsFactory::class,
        ConnectionsHandler::class,
        Coordinator::class,
        CredentialsGatherer::class,
        CredentialsHandler::class,
    ];

    /**
     * {@inheritdoc}
     */
    public function register()
    {
        $this->container->share('remote.local', LocalConnection::class)->withArgument($this->container);
        $this->container->share(ConnectionsFactory::class);
        $this->container->share(ConnectionsHandler::class)->withArgument($this->container);
        $this->container->share(Coordinator::class)->withArgument($this->container);
        $this->container->share(CredentialsGatherer::class)->withArgument($this->container);

        $this->container->share(CredentialsHandler::class, function () {
            $handler = new CredentialsHandler($this->container);
            $handler->register(new ConnectionsKeychain());
            $handler->register(new RepositoriesKeychain());

            return $handler;
        });

        $this->container->share(Bash::class, function () {
            $bash = new Bash($this->container);
            $bash = $this->builder->registerBashModulesOn($bash);

            return $bash;
        });
    }
}
