<?php
namespace Rocketeer\Services\Connections;

use League\Container\ServiceProvider\AbstractServiceProvider;
use Rocketeer\Services\Connections\Connections\LocalConnection;

class ConnectionsServiceProvider extends AbstractServiceProvider
{
    /**
     * @var array
     */
    protected $provides = [
        'remote',
        'remote.local',
        'connections',
        'coordinator',
    ];

    /**
     * Use the register method to register items with the container via the
     * protected $this->container property or the `getContainer` method
     * from the ContainerAwareTrait.
     *
     * @return void
     */
    public function register()
    {
        $this->container->share('remote', function () {
            return new RemoteHandler($this->container);
        });

        $this->container->share('remote.local', function () {
            return new LocalConnection($this->container);
        });

        $this->container->share('connections', function () {
            return new ConnectionsHandler($this->container);
        });

        $this->container->share('coordinator', function () {
            return new Coordinator($this->container);
        });
    }
}
