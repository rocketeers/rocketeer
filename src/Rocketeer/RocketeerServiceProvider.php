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
class RocketeerServiceProvider extends AbstractServiceProvider
{
    /**
     * Register the service provider.
     */
    public function register()
    {
        $this->container->add(Container::class, $this->container);

        $this->bindThirdPartyServices();
        $this->bindPaths();

        // Bind Rocketeer's classes
        $this->bindCoreClasses();
        $this->bindConsoleClasses();
        $this->bindStrategies();

        // Load the user's events, tasks, plugins, and configurations
        $this->container->get('rocketeer.igniter')->loadUserConfiguration();
        $this->container->get('rocketeer.tasks')->registerConfiguredEvents();

        // Bind commands
        $this->bindCommands();
    }

    ////////////////////////////////////////////////////////////////////
    /////////////////////////// CLASS BINDINGS /////////////////////////
    ////////////////////////////////////////////////////////////////////

    /**
     * Bind the Rocketeer paths.
     */
    public function bindPaths()
    {
        $this->container->share('rocketeer.paths', function () {
            $pathfinder = new Pathfinder($this->container);
            $pathfinder->registerPathfinder(LocalPathfinder::class);
            $pathfinder->registerPathfinder(ServerPathfinder::class);

            return $pathfinder;
        });

        $this->share('rocketeer.igniter', ConfigurationIgnition::class);
        $this->share('rocketeer.igniter.tasks', Tasks::class);

        // Bind paths
        $this->container->get('rocketeer.igniter')->bindPaths();
    }

    /**
     * Bind the core classes.
     */
    public function bindThirdPartyServices()
    {
        $this->container->add('flysystem', function () {
            return (new FilesystemsMounter($this->container))->getMountManager();
        });

        $this->container->share('files', function () {
            $local = new Filesystem(new Local('/', LOCK_EX, Local::SKIP_LINKS));
            $local->addPlugin(new RequirePlugin());
            $local->addPlugin(new IsDirectoryPlugin());
            $local->addPlugin(new IncludePlugin());
            $local->addPlugin(new UpsertPlugin());

            return $local;
        });

        //$this->container->bindIf('request', function () {
        //    return Request::createFromGlobals();
        //}, true);

        $this->share('rocketeer.remote', RemoteHandler::class);
        $this->share('remote.local', LocalConnection::class);
        $this->share('events', Emitter::class);

        // Register factory and custom configurations
        $this->registerConfig();
    }

    /**
     * Bind the Rocketeer classes to the Container.
     */
    public function bindCoreClasses()
    {
        $this->share('rocketeer.environment', Environment::class);
        $this->share('rocketeer.timer', QueueTimer::class);
        $this->share('rocketeer.builder', Builder::class);
        $this->container->add('rocketeer.bash', new Bash($this->container));
        $this->share('rocketeer.connections', ConnectionsHandler::class);
        $this->share('rocketeer.coordinator', Coordinator::class);
        $this->share('rocketeer.explainer', QueueExplainer::class);
        $this->share('rocketeer.history', History::class);
        $this->share('rocketeer.logs', LogsHandler::class);
        $this->share('rocketeer.queue', TasksQueue::class);
        $this->share('rocketeer.releases', ReleasesManager::class);
        $this->share('rocketeer.rocketeer', Rocketeer::class);
        $this->share('rocketeer.roles', RolesManager::class);
        $this->share('rocketeer.tasks', TasksHandler::class);

        $this->container->share('rocketeer.storage.local', function () {
            $folder = $this->container->get('rocketeer.paths')->getRocketeerConfigFolder();
            $filename = $this->container->get('rocketeer.rocketeer')->getApplicationName();
            $filename = $filename === '{application_name}' ? 'deployments' : $filename;

            return new Storage($this->container, 'local', $folder, $filename);
        });
    }

    /**
     * Bind the CredentialsGatherer and Console application.
     */
    public function bindConsoleClasses()
    {
        $this->share('rocketeer.credentials.handler', CredentialsHandler::class);
        $this->share('rocketeer.credentials.gatherer', CredentialsGatherer::class);
        $this->share('rocketeer.console', Console::class);
        $this->container->get('rocketeer.credentials.handler')->syncConnectionCredentials();
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

    /**
     * Bind the commands to the Container.
     */
    public function bindCommands()
    {
        // Base commands
        $tasks = $this->container->get('rocketeer.igniter.tasks')->getPredefinedTasks();

        // Register the tasks and their commands
        $commands = $this->container->get('rocketeer.igniter.tasks')->registerTasksAndCommands($tasks);

        // Add commands to Artisan
        foreach ($commands as $command) {
            $this->container->get('rocketeer.console')->add($this->container->get($command));
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
        $this->container->add(LoaderInterface::class, function () {
            $locator = new FileLocator();
            $loader = new LoaderResolver([new PhpLoader($locator)]);
            $loader = new DelegatingLoader($loader);

            return $loader;
        });

        $this->container->share(ConfigurationCache::class, function () {
            return new ConfigurationCache($this->container->get('rocketeer.paths')->getConfigurationCachePath(), false);
        });

        $this->container->share('rocketeer.config.loader', function () {
            $loader = $this->container->get(ConfigurationLoader::class);
            $loader->setFolders([
                __DIR__.'/../config',
                $this->container->get('rocketeer.paths')->getConfigurationPath(),
            ]);

            return $loader;
        });

        $this->container->add('rocketeer.config.publisher', function () {
            return new ConfigurationPublisher(new ConfigurationDefinition(), $this->container->get('files'));
        });

        $this->container->share('rocketeer.config', function () {
            return new Configuration($this->container->get('rocketeer.config.loader')->getConfiguration());
        });
    }

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
