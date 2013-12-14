<?php
namespace Rocketeer\Traits;

use Illuminate\Container\Container;
use Illuminate\Support\Str;
use Rocketeer\TasksQueue;

abstract class Plugin
{
	/**
	 * The path to the configuration folder
	 *
	 * @var string
	 */
	public $configurationFolder;

	/**
	 * Get the package namespace
	 *
	 * @return string
	 */
	public function getNamespace()
	{
		$namespace = str_replace('\\', '/', get_class($this));
		$namespace = Str::snake(basename($namespace));
		$namespace = str_replace('_', '-', $namespace);

		return $namespace;
	}

	/**
	 * Bind additional classes to the Container
	 *
	 * @param Container $app
	 *
	 * @return Container
	 */
	public function register(Container $app)
	{
		return $app;
	}

	/**
	 * Register Tasks with Rocketeer
	 *
	 * @param TasksQueue $queue
	 *
	 * @return void
	 */
	abstract public function onQueue(TasksQueue $queue);
}
