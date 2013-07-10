<?php
namespace Rocketeer\Facades;

use Illuminate\Support\Facades\Facade;

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
		return 'rocketeer.tasks';
	}
}
