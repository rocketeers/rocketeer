<?php

class RollbackTest extends RocketeerTests
{
	public function testCanRollbackRelease()
	{
		$this->task('Rollback')->execute();

		$this->assertEquals(10000000000000, $this->app['rocketeer.releases']->getCurrentRelease());
	}
}
