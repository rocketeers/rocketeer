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

use Illuminate\Config\FileLoader;
use Illuminate\Config\Repository;
use Illuminate\Http\Request;
use Illuminate\Log\Writer;
use Illuminate\Support\ServiceProvider;
use Monolog\Logger;
use Rocketeer\Services\Storages\LocalStorage;

// Define DS
if (!defined('DS')) {
    define('DS', DIRECTORY_SEPARATOR);
}

/**
 * Bind the various Rocketeer classes to a Container
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
        // ...
    }

    /**
     * Bind classes and commands
     */
    public function boot()
    {
        if (!$this->app->bound('Illuminate\Container\Container')) {
            $this->app->instance('Illuminate\Container\Container', $this->app);
        }

        $this->bindPaths();
        $this->bindThirdPartyServices();

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
     * Bind the Rocketeer paths
     */
    public function bindPaths()
    {
        $this->app->singleton('rocketeer.paths', 'Rocketeer\Services\Pathfinder');
        $this->app->bind('rocketeer.igniter', 'Rocketeer\Services\Ignition\Configuration');
        $this->app->bind('rocketeer.igniter.tasks', 'Rocketeer\Services\Ignition\Tasks');

        // Bind paths
        $this->app['rocketeer.igniter']->bindPaths();
    }

    /**
     * Bind the core classes
     */
    public function bindThirdPartyServices()
    {
        $this->app->bindIf('files', 'Illuminate\Filesystem\Filesystem');

        $this->app->bindIf('request', function () {
            return Request::createFromGlobals();
        }, true);

        $this->app->bindIf('config', function ($app) {
            $fileloader = new FileLoader($app['files'], __DIR__.'/../config');

            return new Repository($fileloader, 'config');
        }, true);

        $this->app->bindIf('rocketeer.remote', 'Rocketeer\Services\Connections\RemoteHandler');
        $this->app->singleton('remote.local', 'Rocketeer\Services\Connections\LocalConnection');
        $this->app->bindIf('events', 'Illuminate\Events\Dispatcher', true);

        $this->app->bindIf('log', function () {
            return new Writer(new Logger('rocketeer'));
        }, true);

        // Register factory and custom configurations
        $this->registerConfig();
    }

    /**
     * Bind the Rocketeer classes to the Container
     */
    public function bindCoreClasses()
    {
        $this->app->bind('rocketeer.builder', 'Rocketeer\Services\Tasks\TasksBuilder');
        $this->app->bind('rocketeer.environment', 'Rocketeer\Services\Environment');
        $this->app->bind('rocketeer.timer', 'Rocketeer\Services\Display\QueueTimer');
        $this->app->singleton('rocketeer.bash', 'Rocketeer\Bash');
        $this->app->singleton('rocketeer.connections', 'Rocketeer\Services\Connections\ConnectionsHandler');
        $this->app->singleton('rocketeer.explainer', 'Rocketeer\Services\Display\QueueExplainer');
        $this->app->singleton('rocketeer.history', 'Rocketeer\Services\History\History');
        $this->app->singleton('rocketeer.logs', 'Rocketeer\Services\History\LogsHandler');
        $this->app->singleton('rocketeer.queue', 'Rocketeer\Services\Tasks\TasksQueue');
        $this->app->singleton('rocketeer.releases', 'Rocketeer\Services\ReleasesManager');
        $this->app->singleton('rocketeer.rocketeer', 'Rocketeer\Rocketeer');
        $this->app->singleton('rocketeer.roles', 'Rocketeer\Services\RolesManager');
        $this->app->singleton('rocketeer.tasks', 'Rocketeer\Services\TasksHandler');

        $this->app->singleton('rocketeer.storage.local', function ($app) {
            $filename = $app['rocketeer.rocketeer']->getApplicationName();
            $filename = $filename === '{application_name}' ? 'deployments' : $filename;

            return new LocalStorage($app, $filename);
        });
    }

    /**
     * Bind the CredentialsGatherer and Console application
     */
    public function bindConsoleClasses()
    {
        $this->app->singleton('rocketeer.credentials', 'Rocketeer\Services\CredentialsGatherer');
        $this->app->singleton('rocketeer.console', function () {
            return new Console\Console('Rocketeer', Rocketeer::VERSION);
        });

        $this->app['rocketeer.console']->setLaravel($this->app);
        $this->app['rocketeer.connections']->syncConnectionCredentials();
    }

    /**
     * Bind the SCM instance
     */
    public function bindStrategies()
    {
        // Bind SCM class
        $scm = $this->app['rocketeer.rocketeer']->getOption('scm.scm');
        $scm = 'Rocketeer\Scm\\'.ucfirst($scm);

        $this->app->bind('rocketeer.scm', function ($app) use ($scm) {
            return new $scm($app);
        });

        // Bind strategies
        $strategies = $this->app['rocketeer.rocketeer']->getOption('strategies');
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
     * Bind the commands to the Container
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
            if (isset($this->app['events'])) {
                $this->commands($command);
            }
        }
    }

    ////////////////////////////////////////////////////////////////////
    /////////////////////////////// HELPERS ////////////////////////////
    ////////////////////////////////////////////////////////////////////

    /**
     * Register factory and custom configurations
     */
    protected function registerConfig()
    {
        // Register config file
        $this->app['config']->package('anahkiasen/rocketeer', __DIR__.'/../config');
        $this->app['config']->getLoader();

        // Register custom config
        $set = $this->app['path.rocketeer.config'];
        if (!file_exists($set)) {
            return;
        }

        $this->app['config']->afterLoading('rocketeer', function ($me, $group, $items) use ($set) {
            $customItems = $set.'/'.$group.'.php';
            if (!file_exists($customItems)) {
                return $items;
            }

            $customItems = include $customItems;

            return array_replace($items, $customItems);
        });
    }
}
