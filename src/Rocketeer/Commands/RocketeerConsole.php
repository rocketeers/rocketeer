<?php
namespace Rocketeer\Commands;

use Illuminate\Console\Application;
use Illuminate\Container\Container;
use Rocketeer\RocketeerServiceProvider;

/**
 * A standalone Rocketeer CLI
 */
class RocketeerConsole extends Application
{
	/**
	 * Create the Rocketeer CLI
	 *
	 * @return Application
	 */
	public static function make()
	{
		$app = RocketeerServiceProvider::make();

		return $app['rocketeer.console'];
	}

	/**
	 * Run the CLI directly
	 *
	 * @return integer
	 */
	public static function makeAndRun()
	{
		return static::make()->run();
	}
}