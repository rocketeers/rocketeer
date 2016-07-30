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

namespace Rocketeer\Services\Container;

use League\Container\ReflectionContainer;
use Rocketeer\Console\ConsoleServiceProvider;
use Rocketeer\RocketeerServiceProvider;
use Rocketeer\Services\Bootstrapper\BootstrapperServiceProvider;
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
use Rocketeer\Services\Roles\RolesServiceProvider;
use Rocketeer\Services\Storages\StorageServiceProvider;
use Rocketeer\Services\Tasks\TasksServiceProvider;
use Rocketeer\Strategies\StrategiesServiceProvider;

// Define DS
if (!defined('DS')) {
    define('DS', DIRECTORY_SEPARATOR);
}

/**
 * A fork of the container class that autoloads
 * Rocketeer's service providers on creation.
 */
class Container extends \League\Container\Container
{
    /**
     * @var ServiceProviderAggregate
     */
    protected $providers;

    /**
     * {@inheritdoc}
     */
    public function __construct()
    {
        parent::__construct(new ServiceProviderAggregate());

        // Bind container onto itself
        $this->delegate(new ReflectionContainer());
        $this->add(self::class, $this);

        // Bind service providers
        $providers = [
            BuilderServiceProvider::class,
            ConfigurationServiceProvider::class,
            ConnectionsServiceProvider::class,
            ConsoleServiceProvider::class,
            DisplayServiceProvider::class,
            EnvironmentServiceProvider::class,
            EventsServiceProvider::class,
            FilesystemServiceProvider::class,
            HistoryServiceProvider::class,
            IgnitionServiceProvider::class,
            ReleasesServiceProvider::class,
            RocketeerServiceProvider::class,
            RolesServiceProvider::class,
            StorageServiceProvider::class,
            StrategiesServiceProvider::class,
            TasksServiceProvider::class,
            BootstrapperServiceProvider::class,
        ];

        foreach ($providers as $provider) {
            $this->addServiceProvider($provider);
        }
    }

    ////////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////// PROVIDERS ///////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////

    /**
     * @return string[]
     */
    public function getPlugins()
    {
        return array_keys($this->providers->getPlugins());
    }

    ////////////////////////////////////////////////////////////////////////////////
    /////////////////////////////////// BINDINGS ///////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////

    /**
     * @param string $key
     */
    public function remove($key)
    {
        unset($this->definitions[$key], $this->shared[$key], $this->sharedDefinitions[$key]);
    }

    /**
     * {@inheritdoc}
     */
    public function add($alias, $concrete = null, $share = false)
    {
        $this->remove($alias);

        return parent::add($alias, $concrete, $share);
    }
}
