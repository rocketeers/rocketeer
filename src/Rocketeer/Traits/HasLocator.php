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

/**
 * A trait for Service Locator-based classes wich adds
 * a few shortcuts to Rocketeer classes
 *
 * @property \Illuminate\Config\Repository                                              config
 * @property \Illuminate\Events\Dispatcher                                              events
 * @property \Illuminate\Filesystem\Filesystem                                          files
 * @property \Illuminate\Log\Writer                                                     log
 * @property \Illuminate\Remote\Connection                                              remote
 * @property \Rocketeer\Abstracts\AbstractCommand                                       command
 * @property \Rocketeer\Bash                                                            bash
 * @property \Rocketeer\QueueExplainer                                                  explainer
 * @property \Rocketeer\Console\Console                                                 console
 * @property \Rocketeer\Interfaces\ScmInterface                                         scm
 * @property \Rocketeer\Interfaces\Strategies\DeployStrategyInterface                   strategy
 * @property \Rocketeer\Rocketeer                                                       rocketeer
 * @property \Rocketeer\Services\ConnectionsHandler                                     connections
 * @property \Rocketeer\Services\CredentialsGatherer                                    credentials
 * @property \Rocketeer\Services\History\History                                        history
 * @property \Rocketeer\Services\ReleasesManager                                        releasesManager
 * @property \Rocketeer\Services\Storages\LocalStorage                                  localStorage
 * @property \Rocketeer\Services\Tasks\TasksBuilder                                     builder
 * @property \Rocketeer\Services\Tasks\TasksQueue                                       queue
 * @property \Rocketeer\Services\TasksHandler                                           tasks
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
	 * @param  string $key
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
			'explainer'       => 'rocketeer.explainer',
			'history'         => 'rocketeer.history',
			'localStorage'    => 'rocketeer.storage.local',
			'logs'            => 'rocketeer.logs',
			'queue'           => 'rocketeer.queue',
			'releasesManager' => 'rocketeer.releases',
			'rocketeer'       => 'rocketeer.rocketeer',
			'scm'             => 'rocketeer.scm',
			'strategy'        => 'rocketeer.strategy',
			'tasks'           => 'rocketeer.tasks',
		);

		// Replace shortcuts
		if (array_key_exists($key, $shortcuts)) {
			$key = $shortcuts[$key];
		}

		return $this->app[$key];
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
	 * @param  string $option
	 *
	 * @return string
	 */
	protected function getOption($option)
	{
		return $this->hasCommand() ? $this->command->option($option) : null;
	}
}
