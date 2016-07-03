<?php

/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Rocketeer;

use League\Container\ServiceProvider\AbstractServiceProvider;
use Rocketeer\Console\ConsoleServiceProvider;
use Rocketeer\Services\Builders\BuilderServiceProvider;
use Rocketeer\Services\Config\ConfigurationServiceProvider;
use Rocketeer\Services\Connections\ConnectionsServiceProvider;
use Rocketeer\Services\Display\DisplayServiceProvider;
use Rocketeer\Services\Environment\EnvironmentServiceProvider;
use Rocketeer\Services\Events\EventsServiceProvider;
use Rocketeer\Services\Filesystem\FilesystemServiceProvider;
use Rocketeer\Services\History\HistoryServiceProvider;
use Rocketeer\Services\Ignition\IgnitionServiceProvider;
use Rocketeer\Services\Releases\ReleasesServiceProvider;
use Rocketeer\Services\Storages\StorageServiceProvider;
use Rocketeer\Services\Tasks\TasksServiceProvider;
use Rocketeer\Strategies\StrategiesServiceProvider;

// Define DS
if (!defined('DS')) {
    define('DS', DIRECTORY_SEPARATOR);
}

/**
 * Bind the various Rocketeer classes to a Container.
 *
 * @author Maxime Fabre <ehtnam6@gmail.com>
 */
class RocketeerServiceProvider extends AbstractServiceProvider
{
    /**
     * @var array
     */
    protected $providers = [
        BuilderServiceProvider::class,
        ConfigurationServiceProvider::class,
        ConnectionsServiceProvider::class,
        ConsoleServiceProvider::class,
        DisplayServiceProvider::class,
        EnvironmentServiceProvider::class,
        EventsServiceProvider::class,
        FilesystemServiceProvider::class,
        HistoryServiceProvider::class,
        ReleasesServiceProvider::class,
        StorageServiceProvider::class,
        StrategiesServiceProvider::class,
        TasksServiceProvider::class,
        IgnitionServiceProvider::class,
    ];

    /**
     * Register the service provider.
     */
    public function register()
    {
        $this->container->add(Container::class, $this->container);
        foreach ($this->providers as $provider) {
            $this->container->addServiceProvider(new $provider());
        }

        $this->container->share(Rocketeer::class)->withArgument($this->container);

        // Load the user's events, tasks, plugins, and configurations
        $this->container->get('credentials.handler')->syncConnectionCredentials();
        $this->container->get('tasks')->registerConfiguredEvents();
    }
}
