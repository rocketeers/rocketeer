<?php
namespace Rocketeer;

use Rocketeer\TestCases\RocketeerTestCase;

class BashTest extends RocketeerTestCase
{
	public function testBashIsCorrectlyComposed()
	{
		$contents = $this->task->runRaw('ls', true, true);
		if (count($contents) !== 11) {
			var_dump($contents);
		}

		$this->assertCount(11, $contents);
	}
}
