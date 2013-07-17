<?php

class RocketeerTest extends RocketeerTests
{

	public function testCanGetAvailableConnections()
	{
		$connections = $this->app['rocketeer.rocketeer']->getConnections();
		$this->assertEquals(array('production'), array_keys($connections));

		$this->app['rocketeer.server']->setValue('connections.custom.username', 'foobar');
		$connections = $this->app['rocketeer.rocketeer']->getConnections();
		$this->assertEquals(array('custom'), array_keys($connections));
	}

	public function testCanUseSshRepository()
	{
		$repository = 'git@github.com:Anahkiasen/rocketeer.git';
		$this->app['config']->shouldReceive('get')->with('rocketeer::scm')->andReturn(array(
			'repository' => $repository,
			'username'   => '',
			'password'   => '',
		));

		$this->assertEquals($repository, $this->app['rocketeer.rocketeer']->getRepository());
	}

	public function testCanUseHttpsRepository()
	{
		$this->app['config']->shouldReceive('get')->with('rocketeer::scm')->andReturn(array(
			'repository' => 'https://github.com/Anahkiasen/rocketeer.git',
			'username'   => 'foobar',
			'password'   => 'bar',
		));

		$this->assertEquals('https://foobar:bar@github.com/Anahkiasen/rocketeer.git', $this->app['rocketeer.rocketeer']->getRepository());
	}

	public function testCanUseHttpsRepositoryWithUsernameProvided()
	{
		$this->app['config']->shouldReceive('get')->with('rocketeer::scm')->andReturn(array(
			'repository' => 'https://foobar@github.com/Anahkiasen/rocketeer.git',
			'username'   => 'foobar',
			'password'   => 'bar',
		));

		$this->assertEquals('https://foobar:bar@github.com/Anahkiasen/rocketeer.git', $this->app['rocketeer.rocketeer']->getRepository());
	}

	public function testCanUseHttpsRepositoryWithOnlyUsernameProvided()
	{
		$this->app['config']->shouldReceive('get')->with('rocketeer::scm')->andReturn(array(
			'repository' => 'https://foobar@github.com/Anahkiasen/rocketeer.git',
			'username'   => 'foobar',
			'password'   => '',
		));

		$this->assertEquals('https://foobar@github.com/Anahkiasen/rocketeer.git', $this->app['rocketeer.rocketeer']->getRepository());
	}

	public function testCanUseHttpsRepositoryWithoutCredentials()
	{
		$this->app['config']->shouldReceive('get')->with('rocketeer::scm')->andReturn(array(
			'repository' => 'https://github.com/Anahkiasen/rocketeer.git',
			'username'   => '',
			'password'   => '',
		));

		$this->assertEquals('https://github.com/Anahkiasen/rocketeer.git', $this->app['rocketeer.rocketeer']->getRepository());
	}

	public function testCangetRepositoryBranch()
	{
		$this->assertEquals('master', $this->app['rocketeer.rocketeer']->getRepositoryBranch());
	}

	public function testCanGetApplicationName()
	{
		$this->assertEquals('foobar', $this->app['rocketeer.rocketeer']->getApplicationName());
	}

	public function testCanGetHomeFolder()
	{
		$this->assertEquals($this->server.'', $this->app['rocketeer.rocketeer']->getHomeFolder());
	}

	public function testCanGetFolderWithStage()
	{
		$this->app['rocketeer.rocketeer']->setStage('test');

		$this->assertEquals($this->server.'/test/current', $this->app['rocketeer.rocketeer']->getFolder('current'));
	}

	public function testCanGetAnyFolder()
	{
		$this->assertEquals($this->server.'/current', $this->app['rocketeer.rocketeer']->getFolder('current'));
	}
}
