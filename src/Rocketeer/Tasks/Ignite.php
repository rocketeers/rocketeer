<?php
/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Rocketeer\Tasks;

use Rocketeer\Traits\Task;

/**
 * A task to ignite Rocketeer
 *
 * @author Maxime Fabre <ehtnam6@gmail.com>
 */
class Ignite extends Task
{
	 /**
	 * A description of what the Task does
	 *
	 * @var string
	 */
	protected $description = "Creates Rocketeer's configuration";

	/**
	 * Execute ignite
	 *
	 * @return void
	 */
	public function execute()
	{
		// Export configuration
		$path = $this->command->isInsideLaravel()
			? $this->createLaravelConfiguration()
			: $this->createOutsideConfiguration();

		// Replace placeholders
		$parameters = $this->getConfigurationInformations();
		$this->app['rocketeer.igniter']->updateConfiguration($path, $parameters);

		// Display info
		$folder  = basename(dirname($path)).'/'.basename($path);
		$message = '<comment>The Rocketeer configuration was created at</comment> <info>'.$folder.'</info>';

		return $this->command->line($message);
	}

	/**
	 * Create the configuration outside of Laravel
	 *
	 * @return string
	 */
	protected function createLaravelConfiguration()
	{
		$this->command->call('config:publish', array('package' => 'anahkiasen/rocketeer'));

		return $this->app['path'].'/config/packages/anahkiasen/rocketeer';
	}

	/**
	 * Get the configuration stub to use
	 *
	 * @return string
	 */
	protected function createOutsideConfiguration()
	{
		return $this->app['rocketeer.igniter']->exportConfiguration();
	}

	/**
	 * Get the core informations to inject in the configuration created
	 *
	 * @return array
	 */
	protected function getConfigurationInformations()
	{
		// Replace credentials
		$repositoryCredentials = $this->rocketeer->getCredentials();
		$name = basename($this->app['path.base']);

		return array_merge(
			$this->rocketeer->getConnectionCredentials(),
			array(
				'scm_repository'   => $repositoryCredentials['repository'],
				'scm_username'     => $repositoryCredentials['username'],
				'scm_password'     => $repositoryCredentials['password'],
				'application_name' => $this->command->ask("What is your application's name ? (" .$name. ")", $name),
			)
		);
	}
}
