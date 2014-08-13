<?php
namespace Rocketeer\Strategies;

use Rocketeer\Abstracts\AbstractStrategy;
use Rocketeer\Interfaces\Strategies\MigrateStrategyInterface;

class ArtisanMigrateStrategy extends AbstractStrategy implements MigrateStrategyInterface
{
	/**
	 * Whether this particular strategy is runnable or not
	 *
	 * @return boolean
	 */
	public function isExecutable()
	{
		return $this->artisan()->getBinary();
	}

	/**
	 * Run outstanding migrations
	 *
	 * @return boolean|null
	 */
	public function migrate()
	{
		$this->artisan()->runForCurrentRelease('migrate');
	}

	/**
	 * Seed the database
	 *
	 * @return boolean|null
	 */
	public function seed()
	{
		$this->artisan()->runForCurrentRelease('seed');
	}
}
