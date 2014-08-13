<?php
namespace Rocketeer\Strategies;

use Rocketeer\Abstracts\AbstractStrategy;
use Rocketeer\Interfaces\Strategies\MigrateStrategyInterface;

class ArtisanMigrateStrategy extends AbstractStrategy implements MigrateStrategyInterface
{
	/**
	 * Run outstanding migrations
	 *
	 * @return boolean
	 */
	public function migrate()
	{
		$this->artisan()->runForCurrentRelease('migrate');
	}

	/**
	 * Seed the database
	 *
	 * @return boolean
	 */
	public function seed()
	{
		$this->artisan()->runForCurrentRelease('seed');
	}
}
