<?php

class TeardownTest extends RocketeerTests
{
	public function testCanTeardownServer()
	{
		$this->task('Teardown')->execute();

		$this->assertFileNotExists($this->deploymentsFile);
		$this->assertFileNotExists($this->server);
	}
}