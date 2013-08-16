<?php

class DeployTest extends RocketeerTests
{
	public function testCanDeployToServer()
	{
		$this->app['config']->shouldReceive('get')->with('rocketeer::scm')->andReturn(array(
			'repository' => 'https://github.com/Anahkiasen/rocketeer.git',
			'username'   => '',
			'password'   => '',
		));

		$this->task('Deploy')->execute();
		$release = $this->app['rocketeer.releases']->getCurrentRelease();

		$releasePath = $this->server.'/releases/'.$release;
		$this->assertFileExists($this->server.'/shared/tests/meta/deployments.json');
		$this->assertFileExists($releasePath);
		$this->assertFileExists($releasePath.'/.git');
		$this->assertFileExists($releasePath.'/vendor');

		$this->recreateVirtualServer();
	}
}
