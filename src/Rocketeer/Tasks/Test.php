<?php
namespace Rocketeer\Tasks;

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
