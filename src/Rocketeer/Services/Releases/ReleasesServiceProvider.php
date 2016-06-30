<?php
namespace Rocketeer\Services\Releases;

use League\Container\ServiceProvider\AbstractServiceProvider;

class ReleasesServiceProvider extends AbstractServiceProvider
{
    /**
     * @var array
     */
    protected $provides = [
        ReleasesManager::class,
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
        $this->container->share(ReleasesManager::class, function () {
            return new ReleasesManager($this->container);
        });
    }
}
