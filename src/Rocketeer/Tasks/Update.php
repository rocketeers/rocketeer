<?php
namespace Rocketeer\Tasks;

/**
 * Update the remote server without doing a new release
 */
class Update extends Deploy
{

	 /**
	 * A description of what the Task does
	 *
	 * @var string
	 */
	protected $description = 'Update the remote server without doing a new release';

	/**
	 * Run the Task
	 *
	 * @return  void
	 */
	public function execute()
	{
		// Update repository
		$this->updateRepository();

		// Recompile dependencies and stuff
		$this->runComposer();

		// Set permissions
		$this->setApplicationPermissions();

		// Run migrations
		$this->runMigrations();

		// Clear cache
		$this->runForCurrentRelease('php artisan cache:clear');

		$this->command->info('Successfully updated application');
	}

}
