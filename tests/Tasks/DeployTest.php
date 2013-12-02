<?php

class DeployTest extends RocketeerTests
{
	public function testCanDeployToServer()
	{
		$this->app['config']->shouldReceive('get')->with('rocketeer::scm')->andReturn(array(
			'repository' => 'https://github.com/'.$this->repository,
			'username'   => '',
			'password'   => '',
		));

		$this->task('Deploy')->execute();
		$release = $this->app['rocketeer.releases']->getCurrentRelease();

		$releasePath = $this->server.'/releases/'.$release;
		$this->assertFileExists($this->server.'/shared/tests/Elements/ElementTest.php');
		$this->assertFileExists($releasePath);
		$this->assertFileExists($releasePath.'/.git');
		$this->assertFileExists($releasePath.'/vendor');

		$this->recreateVirtualServer();
	}
}
