<?php
class RocketeerTest extends RocketeerTests
{

	public function testCanUseSshRepository()
	{
		$repository = 'git@github.com:Anahkiasen/rocketeer.git';
		$this->app['config']->shouldReceive('get')->with('rocketeer::git')->andReturn(array(
			'repository' => $repository,
			'username'   => 'foobar',
			'password'   => 'bar',
		));

		$this->assertEquals($repository, $this->app['rocketeer.rocketeer']->getGitRepository());
	}

	public function testCanUseHttpsRepository()
	{
		$this->app['config']->shouldReceive('get')->with('rocketeer::git')->andReturn(array(
			'repository' => 'https://github.com/Anahkiasen/rocketeer.git',
			'username'   => 'foobar',
			'password'   => 'bar',
		));

		$this->assertEquals('https://foobar:bar@github.com/Anahkiasen/rocketeer.git', $this->app['rocketeer.rocketeer']->getGitRepository());
	}

	public function testCanUseHttpsRepositoryWithUsernameProvided()
	{
		$this->app['config']->shouldReceive('get')->with('rocketeer::git')->andReturn(array(
			'repository' => 'https://foobar@github.com/Anahkiasen/rocketeer.git',
			'username'   => 'foobar',
			'password'   => 'bar',
		));

		$this->assertEquals('https://foobar:bar@github.com/Anahkiasen/rocketeer.git', $this->app['rocketeer.rocketeer']->getGitRepository());
	}

	public function testCanGetGitBranch()
	{
		$this->assertEquals('master', $this->app['rocketeer.rocketeer']->getGitBranch());
	}

	public function testCanGetApplicationName()
	{
		$this->assertEquals('foobar', $this->app['rocketeer.rocketeer']->getApplicationName());
	}

	public function testCanGetHomeFolder()
	{
		$this->assertEquals($this->server.'', $this->app['rocketeer.rocketeer']->getHomeFolder());
	}

	public function testCanGetAnyFolder()
	{
		$this->assertEquals($this->server.'/current', $this->app['rocketeer.rocketeer']->getFolder('current'));
	}

}