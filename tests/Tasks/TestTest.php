<?php
namespace Rocketeer\Tests\Tasks;

use Rocketeer\Tests\TestCases\RocketeerTestCase;

class TestTest extends RocketeerTestCase
{
	public function testCanRunTests()
	{
		$tests = $this->pretendTask('Test')->execute();

		$this->assertEquals('cd '.$this->server.'/releases/20000000000000', $tests[0]);
		$this->assertContains('phpunit --stop-on-failure', $tests[1]);
	}
}
