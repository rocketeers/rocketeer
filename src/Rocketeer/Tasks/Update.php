<?php
namespace Rocketeer\Tasks;

class Update extends Task
{

	 /**
	 * A description of what the Task does
	 *
	 * @var string
	 */
	public $description = 'Updates the remote server without doing a new release';

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
