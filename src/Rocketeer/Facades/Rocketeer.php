<?php
namespace Rocketeer\Facades;

use Illuminate\Support\Facades\Facade;
use Rocketeer\RocketeerServiceProvider;

/**
 * A Facade for the TasksQueue class
 */
class Rocketeer extends Facade
{
	/**
	 * Get the registered name of the component.
	 *
	 * @return string
	 */
	protected static function getFacadeAccessor()
	{
		if (!static::$app) {
			static::$app = RocketeerServiceProvider::make();
		}

		return 'rocketeer.tasks';
	}
}
