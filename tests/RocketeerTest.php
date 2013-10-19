<?php

class RocketeerTest extends RocketeerTests
{
	////////////////////////////////////////////////////////////////////
	//////////////////////////////// TESTS /////////////////////////////
	////////////////////////////////////////////////////////////////////

	public function testCanGetAvailableConnections()
	{
		$connections = $this->app['rocketeer.rocketeer']->getAvailableConnections();
		$this->assertEquals(array('production', 'staging'), array_keys($connections));

		$this->app['rocketeer.server']->setValue('connections.custom.username', 'foobar');
		$connections = $this->app['rocketeer.rocketeer']->getAvailableConnections();
		$this->assertEquals(array('custom'), array_keys($connections));
	}

	public function testCanGetCurrentConnection()
	{
		$this->swapConfig(array('rocketeer::connections' => 'foobar'));
		$this->assertEquals('production', $this->app['rocketeer.rocketeer']->getConnection());

		$this->swapConfig(array('rocketeer::connections' => 'production'));
		$this->assertEquals('production', $this->app['rocketeer.rocketeer']->getConnection());

		$this->swapConfig(array('rocketeer::connections' => 'staging'));
		$this->assertEquals('staging', $this->app['rocketeer.rocketeer']->getConnection());
	}

	public function testCanChangeConnection()
	{
		$this->assertEquals('production', $this->app['rocketeer.rocketeer']->getConnection());

		$this->app['rocketeer.rocketeer']->setConnection('staging');
		$this->assertEquals('staging', $this->app['rocketeer.rocketeer']->getConnection());

		$this->app['rocketeer.rocketeer']->setConnections('staging,production');
		$this->assertEquals(array('staging', 'production'), $this->app['rocketeer.rocketeer']->getConnections());
	}

	public function testCanUseSshRepository()
	{
		$repository = 'git@github.com:Anahkiasen/rocketeer.git';
		$this->expectRepositoryConfig($repository, '', '');

		$this->assertEquals($repository, $this->app['rocketeer.rocketeer']->getRepository());
	}

	public function testCanUseHttpsRepository()
	{
		$this->expectRepositoryConfig('https://github.com/Anahkiasen/rocketeer.git', 'foobar', 'bar');

		$this->assertEquals('https://foobar:bar@github.com/Anahkiasen/rocketeer.git', $this->app['rocketeer.rocketeer']->getRepository());
	}

	public function testCanUseHttpsRepositoryWithUsernameProvided()
	{
		$this->expectRepositoryConfig('https://foobar@github.com/Anahkiasen/rocketeer.git', 'foobar', 'bar');

		$this->assertEquals('https://foobar:bar@github.com/Anahkiasen/rocketeer.git', $this->app['rocketeer.rocketeer']->getRepository());
	}

	public function testCanUseHttpsRepositoryWithOnlyUsernameProvided()
	{
		$this->expectRepositoryConfig('https://foobar@github.com/Anahkiasen/rocketeer.git', 'foobar', '');

		$this->assertEquals('https://foobar@github.com/Anahkiasen/rocketeer.git', $this->app['rocketeer.rocketeer']->getRepository());
	}

	public function testCanCleanupProvidedRepositoryFromCredentials()
	{
		$this->expectRepositoryConfig('https://foobar@github.com/Anahkiasen/rocketeer.git', 'Anahkiasen', '');

		$this->assertEquals('https://Anahkiasen@github.com/Anahkiasen/rocketeer.git', $this->app['rocketeer.rocketeer']->getRepository());
	}

	public function testCanUseHttpsRepositoryWithoutCredentials()
	{
		$this->expectRepositoryConfig('https://github.com/Anahkiasen/rocketeer.git', '', '');

		$this->assertEquals('https://github.com/Anahkiasen/rocketeer.git', $this->app['rocketeer.rocketeer']->getRepository());
	}

	public function testCanCheckIfRepositoryNeedsCredentials()
	{
		$this->expectRepositoryConfig('https://github.com/Anahkiasen/rocketeer.git', '', '');
		$this->assertTrue($this->app['rocketeer.rocketeer']->needsCredentials());
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

	public function testCanReplacePatternsInFolders()
	{
		$folder = $this->app['rocketeer.rocketeer']->getFolder('{path.storage}');

		$this->assertEquals($this->server.'/app/storage', $folder);
	}

	public function testCannotReplaceUnexistingPatternsInFolders()
	{
		$folder = $this->app['rocketeer.rocketeer']->getFolder('{path.foobar}');

		$this->assertEquals($this->server.'/', $folder);
	}

	public function testCanUseRecursiveStageConfiguration()
	{
		$this->swapConfig(array(
			'rocketeer::scm.branch'                   => 'master',
			'rocketeer::on.stages.staging.scm.branch' => 'staging',
		));

		$this->assertEquals('master', $this->app['rocketeer.rocketeer']->getOption('scm.branch'));
		$this->app['rocketeer.rocketeer']->setStage('staging');
		$this->assertEquals('staging', $this->app['rocketeer.rocketeer']->getOption('scm.branch'));
	}

	public function testCanUseRecursiveConnectionConfiguration()
	{
		$this->swapConfig(array(
			'rocketeer::connections'                       => 'production',
			'rocketeer::scm.branch'                        => 'master',
			'rocketeer::on.connections.staging.scm.branch' => 'staging',
		));
		$this->assertEquals('master', $this->app['rocketeer.rocketeer']->getOption('scm.branch'));

		$this->swapConfig(array(
			'rocketeer::connections'                       => 'staging',
			'rocketeer::scm.branch'                        => 'master',
			'rocketeer::on.connections.staging.scm.branch' => 'staging',
		));
		$this->assertEquals('staging', $this->app['rocketeer.rocketeer']->getOption('scm.branch'));
	}

	////////////////////////////////////////////////////////////////////
	//////////////////////////////// HELPERS ///////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Make the config return specific SCM config
	 *
	 * @param  string $repository
	 * @param  string $username
	 * @param  string $password
	 *
	 * @return void
	 */
	protected function expectRepositoryConfig($repository, $username, $password)
	{
		$this->app['config']->shouldReceive('get')->with('rocketeer::scm')->andReturn(array(
			'repository' => $repository,
			'username'   => $username,
			'password'   => $password,
		));
	}
}
