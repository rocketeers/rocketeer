<?php
namespace Rocketeer\Services\Ignition;

use League\Container\ServiceProvider\AbstractServiceProvider;
use League\Container\ServiceProvider\BootableServiceProviderInterface;

class IgnitionServiceProvider extends AbstractServiceProvider implements BootableServiceProviderInterface
{
    /**
     * @var array
     */
    protected $provides = [
        'igniter',
        'igniter.tasks',
    ];

    /**
     * {@inheritdoc}
     */
    public function register()
    {
        $this->container->share('igniter', function () {
            return new Configuration($this->container);
        });

        $this->container->share('igniter.tasks', function () {
            return new Tasks($this->container);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function boot()
    {
        $this->register();

        $this->container->get('igniter')->bindPaths();
    }
}
