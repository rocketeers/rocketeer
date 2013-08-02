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
	 * Build a new instance of RocketeerConsole
	 *
	 * @param Container $app
	 */
	public function __construct(Container $app = null)
	{
		parent::__construct();

		// Set container
		$this->laravel = $app;
	}

	/**
	 * Create the Rocketeer CLI
	 *
	 * @param array $runtime Runtime configuration
	 *
	 * @return Application
	 */
	public static function make($runtime = array())
	{
		$app = RocketeerServiceProvider::make();

		// Set base path
		$app['path.base'] = explode('/vendor', __DIR__)[0];

		// Merge runtime configuration
		$config = $app['config']->get('rocketeer::config');
		$config = array_replace_recursive($config, $runtime);
		$app['config']->set('rocketeer::config', $config);

		return $app['rocketeer.console'];
	}

	/**
	 * Run the CLI directly
	 *
	 * @param array $runtime Runtime configuration
	 *
	 * @return integer
	 */
	public static function create($runtime = array())
	{
		return static::make($runtime)->run();
	}
}