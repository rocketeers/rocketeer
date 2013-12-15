<?php
/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Rocketeer\Commands;

/**
 * Flushes any custom storage Rocketeer has created
 *
 * @author Maxime Fabre <ehtnam6@gmail.com>
 */
class FlushCommand extends AbstractDeployCommand
{
	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'deploy:flush';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = "Flushes Rocketeer's cache of credentials";

	/**
	 * Execute the tasks
	 *
	 * @return array
	 */
	public function fire()
	{
		$this->laravel['rocketeer.server']->deleteRepository();
		$this->info("Rocketeer's cache has been properly flushed");
	}
}
