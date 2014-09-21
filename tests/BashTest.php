<?php
namespace Rocketeer;

use Rocketeer\TestCases\RocketeerTestCase;

class BashTest extends RocketeerTestCase
{
	public function testBashIsCorrectlyComposed()
	{
		$contents = $this->task->runRaw('ls', true, true);
		if (count($contents) !== $this->numberFiles) {
			!dd($contents);
		}

		$this->assertCount($this->numberFiles, $contents);
	}
}
