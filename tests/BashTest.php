<?php
namespace Rocketeer;

use Rocketeer\TestCases\RocketeerTestCase;

class BashTest extends RocketeerTestCase
{
	public function testBashIsCorrectlyComposed()
	{
		$contents = $this->task->runRaw('ls', true, true);

		$this->assertCount(12, $contents);
	}
}
