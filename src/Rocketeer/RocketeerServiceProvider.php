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
use Rocketeer\Services\Builders\Builder;
use Rocketeer\Services\Config\ConfigurationServiceProvider;
use Rocketeer\Services\Connections\ConnectionsServiceProvider;
use Rocketeer\Services\Credentials\CredentialsGatherer;
use Rocketeer\Services\Credentials\CredentialsHandler;
use Rocketeer\Services\Credentials\CredentialsServiceProvider;
use Rocketeer\Services\Display\DisplayServiceProvider;
use Rocketeer\Services\Display\QueueExplainer;
use Rocketeer\Services\Display\QueueTimer;
use Rocketeer\Services\Environment\EnvironmentServiceProvider;
use Rocketeer\Services\Events\EventsServiceProvider;
use Rocketeer\Services\Filesystem\FilesystemServiceProvider;
use Rocketeer\Services\History\History;
use Rocketeer\Services\History\LogsHandler;
use Rocketeer\Services\Ignition\IgnitionServiceProvider;
use Rocketeer\Services\ReleasesManager;
use Rocketeer\Services\RolesManager;
use Rocketeer\Services\Storages\StorageServiceProvider;
use Rocketeer\Services\Tasks\TasksServiceProvider;

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
        ConfigurationServiceProvider::class,
        ConnectionsServiceProvider::class,
        ConsoleServiceProvider::class,
        CredentialsServiceProvider::class,
        DisplayServiceProvider::class,
        EnvironmentServiceProvider::class,
        EventsServiceProvider::class,
        FilesystemServiceProvider::class,
        StorageServiceProvider::class,
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

        // Bind Rocketeer's classes
        $this->bindCoreClasses();
        $this->bindStrategies();

        // Load the user's events, tasks, plugins, and configurations
        $this->container->get('igniter')->bindPaths();
        $this->container->get('credentials.handler')->syncConnectionCredentials();
        $this->container->get('igniter')->loadUserConfiguration();
        $this->container->get('tasks')->registerConfiguredEvents();
    }

    /**
     * Bind the Rocketeer classes to the Container.
     */
    public function bindCoreClasses()
    {
        $this->share('rocketeer.builder', Builder::class);
        $this->container->add('rocketeer.bash', new Bash($this->container));
        $this->share('rocketeer.history', History::class);
        $this->share('rocketeer.logs', LogsHandler::class);
        $this->share('rocketeer.releases', ReleasesManager::class);
        $this->share('rocketeer.rocketeer', Rocketeer::class);
        $this->share('rocketeer.roles', RolesManager::class);
    }

    /**
     * Bind the SCM instance.
     */
    public function bindStrategies()
    {
        // Bind SCM class
        $scm = $this->container->get('rocketeer.rocketeer')->getOption('scm.scm');
        $this->container->add('rocketeer.scm', function () use ($scm) {
            return $this->container->get('rocketeer.builder')->buildBinary($scm);
        });

        // Bind strategies
        $strategies = (array) $this->container->get('rocketeer.rocketeer')->getOption('strategies');
        foreach ($strategies as $strategy => $concrete) {
            if (!is_string($concrete) || !$concrete) {
                continue;
            }

            $this->container->share('rocketeer.strategies.'.$strategy, function () use ($strategy, $concrete) {
                return $this->container->get('rocketeer.builder')->buildStrategy($strategy, $concrete);
            });
        }
    }

    ////////////////////////////////////////////////////////////////////
    /////////////////////////////// HELPERS ////////////////////////////
    ////////////////////////////////////////////////////////////////////

    /**
     * @param string $alias
     * @param string $concrete
     */
    protected function share($alias, $concrete)
    {
        $this->container->share($alias, function () use ($concrete) {
            return $this->container->get($concrete);
        });
    }
}
