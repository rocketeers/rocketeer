<?php
namespace Rocketeer\Tasks;

class Update extends Task
{

	/**
	 * Run the Task
	 *
	 * @return  void
	 */
	public function execute()
	{
		// Update repository
		$this->command->info('Pulling changes');
		$this->runForCurrentRelease('git pull');

		// Recompile dependencies and stuff
		$this->runComposer();

		// Run migrations
		$this->runMigrations();
	}

}
