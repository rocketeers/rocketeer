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

use Rocketeer\Traits\Task;

/**
 * Run the tests on the server and displays the output
 *
 * @author Maxime Fabre <ehtnam6@gmail.com>
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
