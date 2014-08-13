<?php
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
	 * @return string
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
