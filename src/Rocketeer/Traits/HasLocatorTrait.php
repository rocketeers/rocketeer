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

namespace Rocketeer\Traits;

use Illuminate\Support\Arr;
use Rocketeer\Binaries\Vcs\VcsInterface;
use Rocketeer\Console\Commands\AbstractCommand;
use Rocketeer\Console\Console;
use Rocketeer\Rocketeer;
use Rocketeer\Services\Bootstrapper\Bootstrapper;
use Rocketeer\Services\Builders\Builder;
use Rocketeer\Services\Config\ConfigurationInterface;
use Rocketeer\Services\Config\ContextualConfiguration;
use Rocketeer\Services\Config\Files\ConfigurationPublisher;
use Rocketeer\Services\Config\Files\Loaders\ConfigurationLoader;
use Rocketeer\Services\Config\Files\Loaders\ConfigurationLoaderInterface;
use Rocketeer\Services\Connections\ConnectionsFactory;
use Rocketeer\Services\Connections\ConnectionsHandler;
use Rocketeer\Services\Connections\Coordinator;
use Rocketeer\Services\Connections\Credentials\CredentialsGatherer;
use Rocketeer\Services\Connections\Credentials\CredentialsHandler;
use Rocketeer\Services\Connections\Shell\Bash;
use Rocketeer\Services\Display\QueueExplainer;
use Rocketeer\Services\Display\QueueTimer;
use Rocketeer\Services\Environment\Environment;
use Rocketeer\Services\Environment\Pathfinder;
use Rocketeer\Services\Events\TaggableEmitter;
use Rocketeer\Services\Filesystem\Filesystem;
use Rocketeer\Services\Filesystem\MountManager;
use Rocketeer\Services\History\History;
use Rocketeer\Services\History\LogsHandler;
use Rocketeer\Services\Ignition\RocketeerIgniter;
use Rocketeer\Services\Releases\ReleasesManager;
use Rocketeer\Services\Roles\RolesManager;
use Rocketeer\Services\Storages\Storage;
use Rocketeer\Services\Tasks\TasksHandler;
use Rocketeer\Services\Tasks\TasksQueue;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * A trait for Service Locator-based classes which adds
 * a few shortcuts to Rocketeer classes.
 *
 * @property \Illuminate\Foundation\Artisan artisan
 * @property AbstractCommand                command
 * @property Bash                           bash
 * @property Bootstrapper                   bootstrapper
 * @property Builder                        builder
 * @property ConfigurationLoader            configurationLoader
 * @property ConfigurationPublisher         configurationPublisher
 * @property ConnectionsFactory             remote
 * @property ConnectionsHandler             connections
 * @property Console                        console
 * @property ConfigurationInterface         config
 * @property Coordinator                    coordinator
 * @property CredentialsGatherer            credentialsGatherer
 * @property CredentialsHandler             credentials
 * @property TaggableEmitter                events
 * @property Environment                    environment
 * @property Filesystem                     files
 * @property History                        history
 * @property LogsHandler                    logs
 * @property MountManager                   filesystems
 * @property Pathfinder                     paths
 * @property QueueExplainer                 explainer
 * @property QueueTimer                     timer
 * @property ReleasesManager                releasesManager
 * @property Rocketeer                      rocketeer
 * @property RocketeerIgniter               igniter
 * @property RolesManager                   roles
 * @property VcsInterface                   vcs
 * @property Storage                        localStorage
 * @property Storage                        remoteStorage
 * @property TasksHandler                   tasks
 * @property TasksQueue                     queue
 */
trait HasLocatorTrait
{
    /**
     * Get an instance from the Container.
     *
     * @param string $key
     *
     * @return object
     */
    public function __get($key)
    {
        $key = $this->getLocatorHandle($key);

        return $this->container->get($key);
    }

    /**
     * Set an instance on the Container.
     *
     * @param string $key
     * @param object $value
     */
    public function __set($key, $value)
    {
        $this->container->add($key, $value);
    }

    /**
     * @param string $key
     *
     * @return string
     */
    protected function getLocatorHandle($key)
    {
        $shortcuts = [
            'bash' => Bash::class,
            'bootstrapper' => Bootstrapper::class,
            'builder' => Builder::class,
            'config' => ContextualConfiguration::class,
            'configurationLoader' => ConfigurationLoaderInterface::class,
            'configurationPublisher' => ConfigurationPublisher::class,
            'connections' => ConnectionsHandler::class,
            'console' => Console::class,
            'coordinator' => Coordinator::class,
            'credentials' => CredentialsHandler::class,
            'credentialsGatherer' => CredentialsGatherer::class,
            'environment' => Environment::class,
            'events' => TaggableEmitter::class,
            'explainer' => QueueExplainer::class,
            'files' => Filesystem::class,
            'filesystems' => MountManager::class,
            'history' => History::class,
            'igniter' => RocketeerIgniter::class,
            'localStorage' => 'storage.local',
            'logs' => LogsHandler::class,
            'paths' => Pathfinder::class,
            'queue' => TasksQueue::class,
            'releasesManager' => ReleasesManager::class,
            'remote' => ConnectionsFactory::class,
            'remoteStorage' => 'storage.remote',
            'rocketeer' => Rocketeer::class,
            'roles' => RolesManager::class,
            'tasks' => TasksHandler::class,
            'timer' => QueueTimer::class,
            'vcs' => VcsInterface::class,
        ];

        // Replace shortcuts
        if (isset($shortcuts[$key])) {
            $key = $shortcuts[$key];

            return $key;
        }

        return $key;
    }

    //////////////////////////////////////////////////////////////////////
    ////////////////////////////// COMMAND ///////////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * Check if the current instance has a Command bound.
     *
     * @return bool
     */
    protected function hasCommand()
    {
        return $this->container->has('command');
    }

    /**
     * Get an option from the Command.
     *
     * @param string $option
     * @param bool   $loose
     *
     * @return string
     */
    public function getOption($option, $loose = false)
    {
        // Verbosity levels
        if ($this->hasCommand() && $option === 'verbose') {
            return $this->command->getVerbosity() > OutputInterface::VERBOSITY_NORMAL;
        }

        // Gather options
        $options = isset($this->options) ? $this->options : [];

        // If we have a command and a matching option, get it
        if ($this->hasCommand()) {
            if (!$loose) {
                return $this->command->option($option);
            }

            $options = array_merge($options, (array) $this->command->option());
        }

        return Arr::get($options, $option);
    }

    //////////////////////////////////////////////////////////////////////
    ////////////////////////////// CONTEXT ///////////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * Check if the class is executed inside a Laravel application.
     *
     * @return \Rocketeer\Strategies\Framework\FrameworkStrategyInterface
     */
    public function getFramework()
    {
        return $this->container->has(Builder::class) ? $this->builder->buildStrategy('Framework') : null;
    }
}
