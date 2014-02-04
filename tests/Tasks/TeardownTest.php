<?php
namespace Rocketeer\Tasks;

use Rocketeer\TestCases\RocketeerTestCase;

class TeardownTest extends RocketeerTestCase
{
	public function testCanTeardownServer()
	{
		$this->mock('rocketeer.server', 'Server', function ($mock) {
			return $mock
				->shouldReceive('getSeparator')->andReturn(DIRECTORY_SEPARATOR)
				->shouldReceive('deleteRepository')->once();
		});

		$this->assertTaskHistory('Teardown', array(
			'rm -rf {server}/',
		));
	}

	public function testCanAbortTeardown()
	{
		$this->mock('rocketeer.server', 'Server', function ($mock) {
			return $mock
				->shouldReceive('getSeparator')->andReturn(DIRECTORY_SEPARATOR)
				->shouldReceive('deleteRepository')->never();
		});

		$task    = $this->pretendTask('Teardown', array(), array('confirm' => false));
		$message = $this->assertTaskHistory($task, array());

		$this->assertEquals('Teardown aborted', $message);
	}
}
