<?php
namespace Illuminate\Support\Facades;

class Rocketeer extends Facade
{

	/**
	 * Get the registered name of the component.
	 *
	 * @return string
	 */
	protected static function getFacadeAccessor()
	{
		return 'rocketeer.tasks';
	}

}