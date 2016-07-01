<?php

/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Rocketeer\Traits;

use Illuminate\Support\Arr;
use Rocketeer\Bash;
use Rocketeer\Container;
use Rocketeer\Services\Connections\ConnectionsFactory;
use Rocketeer\Services\History\History;
use Rocketeer\Services\History\LogsHandler;
use Rocketeer\Services\Releases\ReleasesManager;
use Rocketeer\Services\RolesManager;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * A trait for Service Locator-based classes wich adds
 * a few shortcuts to Rocketeer classes.
 *
 * @property \Rocketeer\Services\Config\ContextualConfiguration              config
 * @property \Rocketeer\Services\Config\Files\ConfigurationLoader            configurationLoader
 * @property \Rocketeer\Services\Config\Files\ConfigurationPublisher         configurationPublisher
 * @property \League\Event\Emitter                                           events
 * @property \League\Flysystem\FilesystemInterface                           files
 * @property \League\Flysystem\MountManager                          flysystem
 * @property \Illuminate\Foundation\Artisan                          artisan
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
 * @property \Rocketeer\Services\Display\QueueTimer                          timer
 * @property \Rocketeer\Services\Environment\Environment                     environment
 * @property \Rocketeer\Services\History\History                             history
 * @property \Rocketeer\Services\History\LogsHandler                         logs
 * @property \Rocketeer\Services\Environment\Pathfinder                      paths
 * @property \Rocketeer\Services\Releases\ReleasesManager                    releasesManager
 * @property \Rocketeer\Services\RolesManager                        roles
 * @property \Rocketeer\Services\Storages\Storage                    localStorage
 * @property \Rocketeer\Services\Builders\Builder                    builder
 * @property \Rocketeer\Services\Tasks\TasksQueue                    queue
 * @property \Rocketeer\Services\Tasks\TasksHandler                  tasks
 *
 * @author Maxime Fabre <ehtnam6@gmail.com>
 */
trait HasLocator
{
    /**
     * The IoC Container.
     *
     * @var Container
     */
    protected $app;

    /**
     * Build a new AbstractTask.
     *
     * @param Container $app
     */
    public function __construct(Container $app)
    {
        $this->app = $app;
    }

    /**
     * @param Container $app
     */
    public function setContainer(Container $app)
    {
        $this->app = $app;
    }

    /**
     * @return Container
     */
    public function getContainer()
    {
        return $this->app;
    }

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

        return $this->app->get($key);
    }

    /**
     * Set an instance on the Container.
     *
     * @param string $key
     * @param object $value
     */
    public function __set($key, $value)
    {
        $this->app->add($key, $value);
    }

    /**
     * @param string $key
     *
     * @return string
     */
    protected function getLocatorHandle($key)
    {
        $shortcuts = [
            'configurationLoader' => 'config.loader',
            'configurationPublisher' => 'config.publisher',
            'credentials' => 'credentials.handler',
            'credentialsGatherer' => 'credentials.gatherer',
            'localStorage' => 'storage.local',
            'remote' => ConnectionsFactory::class,

            'bash' => Bash::class,
            'builder' => 'rocketeer.builder',
            'command' => 'rocketeer.command',
            'history' => History::class,
            'logs' => LogsHandler::class,
            'releasesManager' => ReleasesManager::class,
            'rocketeer' => 'rocketeer.rocketeer',
            'roles' => RolesManager::class,
            'scm' => 'rocketeer.scm',
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
        return $this->app->has('rocketeer.command');
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
        return $this->app->has('rocketeer.builder') ? $this->builder->buildStrategy('Framework') : null;
    }
}
