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
use League\Event\Emitter;
use Rocketeer\Bash;
use Rocketeer\Binaries\Scm\ScmInterface;
use Rocketeer\Console\Console;
use Rocketeer\Rocketeer;
use Rocketeer\Services\Builders\Builder;
use Rocketeer\Services\Config\ContextualConfiguration;
use Rocketeer\Services\Config\Files\ConfigurationPublisher;
use Rocketeer\Services\Connections\ConnectionsFactory;
use Rocketeer\Services\Connections\ConnectionsHandler;
use Rocketeer\Services\Connections\Coordinator;
use Rocketeer\Services\Connections\Credentials\CredentialsGatherer;
use Rocketeer\Services\Connections\Credentials\CredentialsHandler;
use Rocketeer\Services\Display\QueueExplainer;
use Rocketeer\Services\Display\QueueTimer;
use Rocketeer\Services\Environment\Environment;
use Rocketeer\Services\Environment\Pathfinder;
use Rocketeer\Services\History\History;
use Rocketeer\Services\History\LogsHandler;
use Rocketeer\Services\Releases\ReleasesManager;
use Rocketeer\Services\Roles\RolesManager;
use Rocketeer\Services\Tasks\TasksHandler;
use Rocketeer\Services\Tasks\TasksQueue;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * A trait for Service Locator-based classes wich adds
 * a few shortcuts to Rocketeer classes.
 *
 * @property \Rocketeer\Services\Config\ContextualConfiguration                               config
 * @property \Rocketeer\Services\Config\Files\ConfigurationLoader                             configurationLoader
 * @property \Rocketeer\Services\Config\Files\ConfigurationPublisher                          configurationPublisher
 * @property \League\Event\Emitter                                                            events
 * @property \League\Flysystem\FilesystemInterface                                            files
 * @property \League\Flysystem\MountManager                                  flysystem
 * @property \Illuminate\Foundation\Artisan                                  artisan
 * @property \Illuminate\Log\Writer                                          log
 * @property \Rocketeer\Console\Commands\AbstractCommand                     command
 * @property \Rocketeer\Bash                                                 bash
 * @property \Rocketeer\Console\Console                                      console
 * @property \Rocketeer\Binaries\Scm\ScmInterface                            scm
 * @property \Rocketeer\Rocketeer                                            rocketeer
 * @property \Rocketeer\Services\Connections\ConnectionsHandler              connections
 * @property \Rocketeer\Services\Connections\Coordinator                     coordinator
 * @property \Rocketeer\Services\Connections\ConnectionsFactory              remote
 * @property \Rocketeer\Services\Connections\Credentials\CredentialsGatherer credentialsGatherer
 * @property \Rocketeer\Services\Connections\Credentials\CredentialsHandler  credentials
 * @property \Rocketeer\Services\Display\QueueExplainer                      explainer
 * @property \Rocketeer\Services\Display\QueueTimer timer
 * @property \Rocketeer\Services\Environment\Environment                                      environment
 * @property \Rocketeer\Services\History\History                                              history
 * @property \Rocketeer\Services\History\LogsHandler                                          logs
 * @property \Rocketeer\Services\Environment\Pathfinder                                       paths
 * @property \Rocketeer\Services\Releases\ReleasesManager                                     releasesManager
 * @property \Rocketeer\Services\Roles\RolesManager                                           roles
 * @property \Rocketeer\Services\Storages\Storage                                             localStorage
 * @property \Rocketeer\Services\Builders\Builder                                             builder
 * @property \Rocketeer\Services\Tasks\TasksQueue                                             queue
 * @property \Rocketeer\Services\Tasks\TasksHandler                                           tasks
 *
 * @author Maxime Fabre <ehtnam6@gmail.com>
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
            'builder' => Builder::class,
            'command' => 'rocketeer.command',
            'config' => ContextualConfiguration::class,
            'configurationLoader' => 'config.loader',
            'configurationPublisher' => ConfigurationPublisher::class,
            'connections' => ConnectionsHandler::class,
            'console' => Console::class,
            'coordinator' => Coordinator::class,
            'credentials' => CredentialsHandler::class,
            'credentialsGatherer' => CredentialsGatherer::class,
            'environment' => Environment::class,
            'events' => Emitter::class,
            'explainer' => QueueExplainer::class,
            'history' => History::class,
            'localStorage' => 'storage.local',
            'logs' => LogsHandler::class,
            'paths' => Pathfinder::class,
            'queue' => TasksQueue::class,
            'releasesManager' => ReleasesManager::class,
            'remote' => ConnectionsFactory::class,
            'rocketeer' => Rocketeer::class,
            'roles' => RolesManager::class,
            'scm' => ScmInterface::class,
            'tasks' => TasksHandler::class,
            'timer' => QueueTimer::class,
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
        return $this->container->has('rocketeer.command');
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
     * @return \Rocketeer\Interfaces\Strategies\FrameworkStrategyInterface
     */
    public function getFramework()
    {
        return $this->container->has(Builder::class) ? $this->builder->buildStrategy('Framework') : null;
    }
}
