<?php
namespace Rocketeer\Tasks;

use Rocketeer\TestCases\RocketeerTestCase;

class TeardownTest extends RocketeerTestCase
{
	public function testCanTeardownServer()
	{
		$this->mock('rocketeer.storage.local', 'LocalStorage', function ($mock) {
			return $mock
				->shouldReceive('getSeparator')->andReturn(DIRECTORY_SEPARATOR)
				->shouldReceive('destroy')->once();
		});

		$this->assertTaskHistory('Teardown', array(
			'rm -rf {server}/',
		));
	}

	public function testCanAbortTeardown()
	{
		$this->mock('rocketeer.storage.local', 'LocalStorage', function ($mock) {
			return $mock
				->shouldReceive('getSeparator')->andReturn(DIRECTORY_SEPARATOR)
				->shouldReceive('destroy')->never();
		});

		$task    = $this->pretendTask('Teardown', array(), array('confirm' => false));
		$message = $this->assertTaskHistory($task, array());

		$this->assertEquals('Teardown aborted', $message);
	}
}
