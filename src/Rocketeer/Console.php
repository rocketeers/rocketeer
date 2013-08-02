<?php
namespace Rocketeer;

use Illuminate\Console\Application;

/**
 * A standalone Rocketeer CLI
 */
class Console extends Application
{
	/**
	 * Set options at runtime
	 *
	 * @param  array  $runtime
	 *
	 * @return void
	 */
	public function config($runtime = array())
	{
		// Get and merge config
		$config = $this->app['config']->get('rocketeer::config');
		$config = array_replace_recursive($config, $runtime);

		// Save merged config
		return $this->app['config']->set('rocketeer::config', $config);
	}
}
