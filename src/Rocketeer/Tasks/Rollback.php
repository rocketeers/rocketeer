<?php
namespace Rocketeer\Tasks;

use Rocketeer\Traits\Task;

/**
 * Rollback to the previous release, or to a specific one
 */
class Rollback extends Task
{

	/**
	 * Run the Task
	 *
	 * @return  void
	 */
	public function execute()
	{
		$rollbackRelease = $this->getRollbackRelease();

		$this->command->info('Rolling back to release '.$rollbackRelease);
		$this->updateSymlink($rollbackRelease);
	}

	////////////////////////////////////////////////////////////////////
	/////////////////////////////// HELPERS ////////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Get the release to rollback to
	 *
	 * @return integer
	 */
	protected function getRollbackRelease()
	{
		return array_get($this->command->argument(), 'release', $this->releasesManager->getPreviousRelease());
	}
}
