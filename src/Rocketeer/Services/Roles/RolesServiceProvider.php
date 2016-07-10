<?php
namespace Rocketeer\Services\Roles;

use League\Container\ServiceProvider\AbstractServiceProvider;

class RolesServiceProvider extends AbstractServiceProvider
{
    /**
     * @var array
     */
    protected $provides = [
        RolesManager::class,
    ];

    /**
     * {@inheritdoc}
     */
    public function register()
    {
        $this->container->share(RolesManager::class)->withArgument($this->container);
    }
}
