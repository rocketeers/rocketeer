<?php
namespace Rocketeer\Services\Connections;

use Rocketeer\TestCases\RocketeerTestCase;

class LocalConnectionTest extends RocketeerTestCase
{
	public function testCanGetPreviousStatus()
	{
		$task = $this->task;
		$task->setLocal(true);
		$task->run('ls');

		$this->assertTrue($task->status());
	}
}
