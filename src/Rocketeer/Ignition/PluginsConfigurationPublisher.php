<?php
/*
* This file is part of Rocketeer
*
* (c) Maxime Fabre <ehtnam6@gmail.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/
namespace Rocketeer\Ignition;

use Rocketeer\Traits\HasLocator;

/**
 * Publishes the plugin's configurations in user-land
 *
 * @author Maxime Fabre <ehtnam6@gmail.com>
 */
class PluginsConfigurationPublisher
{
	use HasLocator;

	/**
	 * Publishes a package's configuration
	 *
	 * @param string $package
	 */
	public function publish($package)
	{
		if ($this->isInsideLaravel()) {
			$this->publishLaravelConfiguration($package);
		}
	}

	/**
	 * Publishes a configuration within a Laravel application
	 *
	 * @param string $package
	 */
	protected function publishLaravelConfiguration($package)
	{
		// Publish initial configuration
		$this->artisan->call('config:publish', ['package' => $package]);

		// Move under Rocketeer namespace
		$path        = $this->app['path'].'/config/packages/'.$package;
		$destination = preg_replace('/packages\/([^\/]+)/', 'packages/rocketeers', $path);

		$this->files->move($path, $destination);
	}
}
