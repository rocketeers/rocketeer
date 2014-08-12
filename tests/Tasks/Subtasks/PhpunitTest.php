<?php
namespace Rocketeer\Tasks\Subtasks;

use Rocketeer\TestCases\RocketeerTestCase;

class PhpunitTest extends RocketeerTestCase
{
	public function testCanRunTests()
	{
		$this->assertTaskHistory('Phpunit', array(
			array(
				'cd {server}/releases/20000000000000',
				'{phpunit} --stop-on-failure',
			),
		));
	}
}
