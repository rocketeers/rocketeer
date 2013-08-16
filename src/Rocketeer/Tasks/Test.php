<?php
namespace Rocketeer\Tasks;

use Rocketeer\Traits\Task;

/**
 * Run the tests on the server and displays the output
 */
class Test extends Task
{
	 /**
	 * A description of what the Task does
	 *
	 * @var string
	 */
	protected $description = 'Run the tests on the server and displays the output';

	/**
	 * Run the Task
	 *
	 * @return  void
	 */
	public function execute()
	{
		// Update repository
		$this->command->info('Testing the application');

		return $this->runTests();
	}
}
