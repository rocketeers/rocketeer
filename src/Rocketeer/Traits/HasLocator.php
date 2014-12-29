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

use Illuminate\Container\Container;
use Illuminate\Support\Arr;

/**
 * A trait for Service Locator-based classes wich adds
 * a few shortcuts to Rocketeer classes
 *
 * @property \Illuminate\Config\Repository                       config
 * @property \Illuminate\Events\Dispatcher                       events
 * @property \Illuminate\Filesystem\Filesystem                   files
 * @property \Illuminate\Foundation\Artisan                      artisan
 * @property \Illuminate\Log\Writer                              log
 * @property \Rocketeer\Abstracts\AbstractCommand                command
 * @property \Rocketeer\Bash                                     bash
 * @property \Rocketeer\Console\Console                          console
 * @property \Rocketeer\Interfaces\ScmInterface                  scm
 * @property \Rocketeer\Rocketeer                                rocketeer
 * @property \Rocketeer\Services\Connections\ConnectionsHandler  connections
 * @property \Rocketeer\Services\Connections\RemoteHandler       remote
 * @property \Rocketeer\Services\Environment                     environment
 * @property \Rocketeer\Services\CredentialsGatherer             credentials
 * @property \Rocketeer\Services\Display\QueueExplainer          explainer
 * @property \Rocketeer\Services\Display\QueueTimer              timer
 * @property \Rocketeer\Services\History\History                 history
 * @property \Rocketeer\Services\History\LogsHandler             logs
 * @property \Rocketeer\Services\Pathfinder                      paths
 * @property \Rocketeer\Services\ReleasesManager                 releasesManager
 * @property \Rocketeer\Services\Storages\LocalStorage           localStorage
 * @property \Rocketeer\Services\Tasks\TasksBuilder              builder
 * @property \Rocketeer\Services\Tasks\TasksQueue                queue
 * @property \Rocketeer\Services\TasksHandler                    tasks
 * @author Maxime Fabre <ehtnam6@gmail.com>
 */
trait HasLocator
{
    /**
     * The IoC Container
     *
     * @var Container
     */
    protected $app;

    /**
     * Build a new AbstractTask
     *
     * @param Container $app
     */
    public function __construct(Container $app)
    {
        $this->app = $app;
    }

    /**
     * Get an instance from the Container
     *
     * @param string $key
     *
     * @return object
     */
    public function __get($key)
    {
        $shortcuts = array(
            'bash'            => 'rocketeer.bash',
            'builder'         => 'rocketeer.builder',
            'command'         => 'rocketeer.command',
            'connections'     => 'rocketeer.connections',
            'console'         => 'rocketeer.console',
            'credentials'     => 'rocketeer.credentials',
            'environment'     => 'rocketeer.environment',
            'explainer'       => 'rocketeer.explainer',
            'history'         => 'rocketeer.history',
            'localStorage'    => 'rocketeer.storage.local',
            'logs'            => 'rocketeer.logs',
            'paths'           => 'rocketeer.paths',
            'queue'           => 'rocketeer.queue',
            'releasesManager' => 'rocketeer.releases',
            'remote'          => 'rocketeer.remote',
            'rocketeer'       => 'rocketeer.rocketeer',
            'scm'             => 'rocketeer.scm',
            'tasks'           => 'rocketeer.tasks',
            'timer'           => 'rocketeer.timer',
        );

        // Replace shortcuts
        if (isset($shortcuts[$key])) {
            $key = $shortcuts[$key];
        }

        return $this->app->make($key);
    }

    /**
     * Set an instance on the Container
     *
     * @param string $key
     * @param object $value
     */
    public function __set($key, $value)
    {
        $this->app[$key] = $value;
    }

    //////////////////////////////////////////////////////////////////////
    ////////////////////////////// COMMAND ///////////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * Check if the current instance has a Command bound
     *
     * @return boolean
     */
    protected function hasCommand()
    {
        return $this->app->bound('rocketeer.command');
    }

    /**
     * Get an option from the Command
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
            return $this->command->getOutput()->getVerbosity();
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
     * Check if the class is executed inside a Laravel application
     *
     * @return boolean
     */
    public function isInsideLaravel()
    {
        return $this->app->bound('path');
    }
}
