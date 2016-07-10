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

namespace Rocketeer;

use League\Container\ServiceProvider\AbstractServiceProvider;
use League\Container\ServiceProvider\BootableServiceProviderInterface;
use Rocketeer\Traits\HasLocator;

/**
 * Bind the various Rocketeer classes to a Container.
 *
 * @author Maxime Fabre <ehtnam6@gmail.com>
 */
class RocketeerServiceProvider extends AbstractServiceProvider implements BootableServiceProviderInterface
{
    use HasLocator;

    /**
     * @var array
     */
    protected $provides = [
        Rocketeer::class,
    ];

    /**
     * Register the service provider.
     */
    public function register()
    {
        $this->container->share(Rocketeer::class)->withArgument($this->container);
    }

    /**
     * Method will be invoked on registration of a service provider implementing
     * this interface. Provides ability for eager loading of Service Providers.
     */
    public function boot()
    {
        // Load the user's events, tasks, plugins, and configurations
        $this->credentials->syncConnectionCredentials();
        $this->tasks->registerConfiguredEvents();
    }
}
