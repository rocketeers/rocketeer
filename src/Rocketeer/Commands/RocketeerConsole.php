<?php
namespace Rocketeer\Commands;

use Illuminate\Console\Application;
use Illuminate\Container\Container;

/**
 * A standalone Rocketeer CLI
 */
class RocketeerConsole extends Application
{
	/**
	 * Bind the container to the CLI
	 *
	 * @param Container $app
	 */
	public function __construct(Container $app)
	{
		parent::__construct();

		$this->laravel = $app;
	}
}