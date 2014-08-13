<?php
namespace Rocketeer\Interfaces\Strategies;

/**
 * Interface for the various migration strategies
 *
 * @author Maxime Fabre <ehtnam6@gmail.com>
 */
interface MigrateStrategyInterface
{
	/**
	 * Run outstanding migrations
	 *
	 * @return boolean
	 */
	public function migrate();

	/**
	 * Seed the database
	 *
	 * @return boolean
	 */
	public function seed();
}
