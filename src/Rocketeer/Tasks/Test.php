<?php
namespace Rocketeer\Tasks;

/**
 * Run the tests on the server and displays the ouput
 */
class Test extends Task
{

	/**
	 * Run the Task
	 *
	 * @return  void
	 */
	public function execute()
	{
		// Update repository
		$this->command->info('Testing the application');
		$this->runTests();
	}

}
