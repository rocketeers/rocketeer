<?php
namespace Rocketeer\Tasks\Subtasks;

use Rocketeer\Abstracts\AbstractTask;

class Phpunit extends AbstractTask
{
	/**
	 * Run the task
	 *
	 * @return string
	 */
	public function execute()
	{
		// Look for PHPUnit
		$phpunit = $this->phpunit();
		if (!$phpunit->getBinary()) {
			return true;
		}

		// Run PHPUnit
		$this->command->info('Running tests');
		$arguments = ['--stop-on-failure' => null];
		$output    = $this->runForCurrentRelease(array(
			$phpunit->getCommand(null, [], $arguments),
		));

		$status = $this->checkStatus('Tests failed', $output, 'Tests passed successfully');
		if (!$status) {
			$this->command->error('Tests failed');
		}

		return $status;
	}
}
