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

use Rocketeer\Abstracts\AbstractTask;

class Migrate extends AbstractTask
{
	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Migrates and/or seed the database';

	/**
	 * Run the task
	 *
	 * @return boolean|null
	 */
	public function execute()
	{
		$strategy = $this->getStrategy('Migrate');
		if (!$strategy) {
			return true;
		}

		if ($this->getOption('migrate')) {
			$this->command->comment('Running outstanding migrations');
			$strategy->migrate();
		}

		if ($this->getOption('seed')) {
			$this->command->comment('Seeding database');
			$strategy->seed();
		}
	}
}
