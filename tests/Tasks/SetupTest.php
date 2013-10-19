<?php

class SetupTest extends RocketeerTests
{
	public function testCanSetupServer()
	{
		$this->app['files']->deleteDirectory($this->server);
		$this->task('Setup')->execute();

		$this->assertFileExists($this->server);
		$this->assertFileExists($this->server.'/current');
		$this->assertFileExists($this->server.'/releases');
	}
}
