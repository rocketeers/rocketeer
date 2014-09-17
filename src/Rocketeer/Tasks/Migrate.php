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
	 * @return boolean|boolean[]
	 */
	public function execute()
	{
		$results = [];

		// Get strategy and options
		$migrate  = $this->getOption('migrate');
		$seed     = $this->getOption('seed');
		$strategy = $this->getStrategy('Migrate');

		// Cancel if nothing to run
		if (!$strategy || (!$migrate && !$seed)) {
			return true;
		}

		// Migrate the database
		if ($migrate) {
			$this->explainer->line('Running outstanding migrations');
			$results[] = $strategy->migrate();
		}

		// Seed it
		if ($seed) {
			$this->explainer->line('Seeding database');
			$results[] = $strategy->seed();
		}

		return $results;
	}
}
