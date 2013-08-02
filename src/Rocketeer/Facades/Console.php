<?php
namespace Rocketeer\Facades;

use Illuminate\Support\Facades\Facade;
use Rocketeer\RocketeerServiceProvider;

/**
 * Facade for Rocketeer's CLI
 */
class Console extends Facade
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
			static::$app['path.base'] = explode('/vendor', __DIR__)[0];
		}

		return 'rocketeer.console';
	}
}
