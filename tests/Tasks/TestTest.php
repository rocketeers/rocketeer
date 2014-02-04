<?php
namespace Rocketeer\Tasks;

use Rocketeer\TestCases\RocketeerTestCase;

class TestTest extends RocketeerTestCase
{
	public function testCanRunTests()
	{
		$this->assertTaskHistory('Test', array(
			array(
				'cd {server}/releases/20000000000000',
				'{phpunit} --stop-on-failure ',
			),
		));
	}
}
