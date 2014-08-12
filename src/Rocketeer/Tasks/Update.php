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

/**
 * Update the remote server without doing a new release
 *
 * @author Maxime Fabre <ehtnam6@gmail.com>
 */
class Update extends Deploy
{
	/**
	 * A description of what the task does
	 *
	 * @var string
	 */
	protected $description = 'Update the remote server without doing a new release';

	/**
	 * Run the task
	 *
	 * @return  void
	 */
	public function execute()
	{
		// Update repository
		$this->strategy->update();

		// Recreate symlinks if necessary
		$this->syncSharedFolders();

		// Recompile dependencies and stuff
		$this->executeTask('Composer');

		// Set permissions
		$this->setApplicationPermissions();

		// Run migrations
		$this->executeTask('Artisan');

		// Clear cache
		$this->artisan()->runForCurrentRelease('clearCache');

		$this->command->info('Successfully updated application');
	}
}
