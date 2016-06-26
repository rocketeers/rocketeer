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

use Illuminate\Contracts\Container\Container;
use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;
use League\Event\Emitter;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use Rocketeer\Console\Console;
use Rocketeer\Services\Builders\Builder;
use Rocketeer\Services\Config\Configuration;
use Rocketeer\Services\Config\ConfigurationCache;
use Rocketeer\Services\Config\ConfigurationDefinition;
use Rocketeer\Services\Config\ConfigurationLoader;
use Rocketeer\Services\Config\ConfigurationPublisher;
use Rocketeer\Services\Connections\Connections\LocalConnection;
use Rocketeer\Services\Connections\ConnectionsHandler;
use Rocketeer\Services\Connections\Coordinator;
use Rocketeer\Services\Connections\RemoteHandler;
use Rocketeer\Services\Credentials\CredentialsGatherer;
use Rocketeer\Services\Credentials\CredentialsHandler;
use Rocketeer\Services\Display\QueueExplainer;
use Rocketeer\Services\Display\QueueTimer;
use Rocketeer\Services\Environment\Environment;
use Rocketeer\Services\Environment\Pathfinder;
use Rocketeer\Services\Environment\Pathfinders\LocalPathfinder;
use Rocketeer\Services\Environment\Pathfinders\ServerPathfinder;
use Rocketeer\Services\Filesystem\FilesystemsMounter;
use Rocketeer\Services\Filesystem\Plugins\IncludePlugin;
use Rocketeer\Services\Filesystem\Plugins\IsDirectoryPlugin;
use Rocketeer\Services\Filesystem\Plugins\RequirePlugin;
use Rocketeer\Services\Filesystem\Plugins\UpsertPlugin;
use Rocketeer\Services\History\History;
use Rocketeer\Services\History\LogsHandler;
use Rocketeer\Services\Ignition\Configuration as ConfigurationIgnition;
use Rocketeer\Services\Ignition\Tasks;
use Rocketeer\Services\ReleasesManager;
use Rocketeer\Services\RolesManager;
use Rocketeer\Services\Storages\Storage;
use Rocketeer\Services\Tasks\TasksQueue;
use Rocketeer\Services\TasksHandler;
use Symfony\Component\Config\Definition\Loaders\PhpLoader;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\DelegatingLoader;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Config\Loader\LoaderResolver;

// Define DS
if (!defined('DS')) {
    define('DS', DIRECTORY_SEPARATOR);
}

/**
 * Bind the various Rocketeer classes to a Container.
 *
 * @author Maxime Fabre <ehtnam6@gmail.com>
 */
class RocketeerServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     */
    public function register()
    {
        if (!$this->app->bound(Container::class)) {
            $this->app->instance(Container::class, $this->app);
        }

        $this->bindThirdPartyServices();
        $this->bindPaths();

        // Bind Rocketeer's classes
        $this->bindCoreClasses();
        $this->bindConsoleClasses();
        $this->bindStrategies();

        // Load the user's events, tasks, plugins, and configurations
        $this->app['rocketeer.igniter']->loadUserConfiguration();
        $this->app['rocketeer.tasks']->registerConfiguredEvents();

        // Bind commands
        $this->bindCommands();
    }

    /**
     * Get the services provided by the provider.
     *
     * @return string[]
     */
    public function provides()
    {
        return ['rocketeer'];
    }

    ////////////////////////////////////////////////////////////////////
    /////////////////////////// CLASS BINDINGS /////////////////////////
    ////////////////////////////////////////////////////////////////////

    /**
     * Bind the Rocketeer paths.
     */
    public function bindPaths()
    {
        $this->app->singleton('rocketeer.paths', function ($app) {
            $pathfinder = new Pathfinder($app);
            $pathfinder->registerPathfinder(LocalPathfinder::class);
            $pathfinder->registerPathfinder(ServerPathfinder::class);

            return $pathfinder;
        });

        $this->app->bind('rocketeer.igniter', ConfigurationIgnition::class);
        $this->app->bind('rocketeer.igniter.tasks', Tasks::class);

        // Bind paths
        $this->app['rocketeer.igniter']->bindPaths();
    }

    /**
     * Bind the core classes.
     */
    public function bindThirdPartyServices()
    {
        $this->app->singleton('flysystem', function ($app) {
            return (new FilesystemsMounter($app))->getMountManager();
        });

        $this->app->singleton('files', function () {
            $local = new Filesystem(new Local('/', LOCK_EX, Local::SKIP_LINKS));
            $local->addPlugin(new RequirePlugin());
            $local->addPlugin(new IsDirectoryPlugin());
            $local->addPlugin(new IncludePlugin());
            $local->addPlugin(new UpsertPlugin());

            return $local;
        });

        $this->app->bindIf('request', function () {
            return Request::createFromGlobals();
        }, true);

        $this->app->bindIf('rocketeer.remote', RemoteHandler::class, true);
        $this->app->singleton('remote.local', LocalConnection::class);
        $this->app->bindIf('events', Emitter::class, true);

        // Register factory and custom configurations
        $this->registerConfig();
    }

    /**
     * Bind the Rocketeer classes to the Container.
     */
    public function bindCoreClasses()
    {
        $this->app->bind('rocketeer.environment', Environment::class);
        $this->app->bind('rocketeer.timer', QueueTimer::class);
        $this->app->singleton('rocketeer.builder', Builder::class);
        $this->app->singleton('rocketeer.bash', Bash::class);
        $this->app->singleton('rocketeer.connections', ConnectionsHandler::class);
        $this->app->singleton('rocketeer.coordinator', Coordinator::class);
        $this->app->singleton('rocketeer.explainer', QueueExplainer::class);
        $this->app->singleton('rocketeer.history', History::class);
        $this->app->singleton('rocketeer.logs', LogsHandler::class);
        $this->app->singleton('rocketeer.queue', TasksQueue::class);
        $this->app->singleton('rocketeer.releases', ReleasesManager::class);
        $this->app->singleton('rocketeer.rocketeer', Rocketeer::class);
        $this->app->singleton('rocketeer.roles', RolesManager::class);
        $this->app->singleton('rocketeer.tasks', TasksHandler::class);

        $this->app->singleton('rocketeer.storage.local', function ($app) {
            $folder = $app['rocketeer.paths']->getRocketeerConfigFolder();
            $filename = $app['rocketeer.rocketeer']->getApplicationName();
            $filename = $filename === '{application_name}' ? 'deployments' : $filename;

            return new Storage($app, 'local', $folder, $filename);
        });
    }

    /**
     * Bind the CredentialsGatherer and Console application.
     */
    public function bindConsoleClasses()
    {
        $this->app->singleton('rocketeer.credentials.handler', CredentialsHandler::class);
        $this->app->singleton('rocketeer.credentials.gatherer', CredentialsGatherer::class);
        $this->app->singleton('rocketeer.console', Console::class);
        $this->app['rocketeer.credentials.handler']->syncConnectionCredentials();
    }

    /**
     * Bind the SCM instance.
     */
    public function bindStrategies()
    {
        // Bind SCM class
        $scm = $this->app['rocketeer.rocketeer']->getOption('scm.scm');
        $this->app->bind('rocketeer.scm', function ($app) use ($scm) {
            return $app['rocketeer.builder']->buildBinary($scm);
        });

        // Bind strategies
        $strategies = (array) $this->app['rocketeer.rocketeer']->getOption('strategies');
        foreach ($strategies as $strategy => $concrete) {
            if (!is_string($concrete) || !$concrete) {
                continue;
            }

            $this->app->singleton('rocketeer.strategies.'.$strategy, function ($app) use ($strategy, $concrete) {
                return $app['rocketeer.builder']->buildStrategy($strategy, $concrete);
            });
        }
    }

    /**
     * Bind the commands to the Container.
     */
    public function bindCommands()
    {
        // Base commands
        $tasks = $this->app['rocketeer.igniter.tasks']->getPredefinedTasks();

        // Register the tasks and their commands
        $commands = $this->app['rocketeer.igniter.tasks']->registerTasksAndCommands($tasks);

        // Add commands to Artisan
        foreach ($commands as $command) {
            $this->app['rocketeer.console']->add($this->app[$command]);
        }
    }

    ////////////////////////////////////////////////////////////////////
    /////////////////////////////// HELPERS ////////////////////////////
    ////////////////////////////////////////////////////////////////////

    /**
     * Register factory and custom configurations.
     */
    protected function registerConfig()
    {
        $this->app->bind(LoaderInterface::class, function () {
            $locator = new FileLocator();
            $loader = new LoaderResolver([new PhpLoader($locator)]);
            $loader = new DelegatingLoader($loader);

            return $loader;
        });

        $this->app->singleton(ConfigurationCache::class, function ($app) {
            return new ConfigurationCache($app['rocketeer.paths']->getConfigurationCachePath(), false);
        });

        $this->app->singleton('rocketeer.config.loader', function ($app) {
            $loader = $app->make(ConfigurationLoader::class);
            $loader->setFolders([__DIR__.'/../config', $app['rocketeer.paths']->getConfigurationPath()]);

            return $loader;
        });

        $this->app->bind('rocketeer.config.publisher', function ($app) {
            return new ConfigurationPublisher(new ConfigurationDefinition(), $app['files']);
        });

        $this->app->singleton('rocketeer.config', function ($app) {
            return new Configuration($app['rocketeer.config.loader']->getConfiguration());
        });
    }
}
