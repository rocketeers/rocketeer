<?php
namespace Rocketeer\Services;

use Rocketeer\TestCases\RocketeerTestCase;

class PathfinderTest extends RocketeerTestCase
{
	public function testCanGetHomeFolder()
	{
		$this->assertEquals($this->server, $this->paths->getHomeFolder());
	}

	public function testCanGetFolderWithStage()
	{
		$this->connections->setStage('test');

		$this->assertEquals($this->server.'/test/current', $this->paths->getFolder('current'));
	}

	public function testCanGetAnyFolder()
	{
		$this->assertEquals($this->server.'/current', $this->paths->getFolder('current'));
	}

	public function testCanReplacePatternsInFolders()
	{
		$folder = $this->paths->getFolder('{path.storage}');

		$this->assertEquals($this->server.'/app/storage', $folder);
	}

	public function testCannotReplaceUnexistingPatternsInFolders()
	{
		$folder = $this->paths->getFolder('{path.foobar}');

		$this->assertEquals($this->server.'/', $folder);
	}

	public function testCanReplacePlaceholdersOnWindows()
	{
		$this->app['path.base']   = 'c:\xampp\htdocs\project';
		$this->app['path.foobar'] = 'c:\xampp\htdocs\project\lol';

		$this->assertEquals($this->server.'/lol', $this->paths->getFolder('{path.foobar}'));
	}

	public function testCanGetUserHomeFolder()
	{
		$_SERVER['HOME'] = '/some/folder';
		$home            = $this->paths->getUserHomeFolder();

		$this->assertEquals('/some/folder', $home);
	}

	public function testCanGetWindowsHomeFolder()
	{
		$_SERVER['HOME']      = null;
		$_SERVER['HOMEDRIVE'] = 'C:';
		$_SERVER['HOMEPATH']  = '\Users\someuser';
		$home                 = $this->paths->getUserHomeFolder();

		$this->assertEquals('C:\Users\someuser', $home);
	}

	public function testCancelsIfNoHomeFolder()
	{
		$this->setExpectedException('Exception');

		$_SERVER['HOME']      = null;
		$_SERVER['HOMEDRIVE'] = 'C:';
		$_SERVER['HOMEPATH']  = null;
		$this->paths->getUserHomeFolder();
	}

	public function testCanGetRocketeerFolder()
	{
		$_SERVER['HOME'] = '/some/folder';
		$rocketeer       = $this->paths->getRocketeerConfigFolder();

		$this->assertEquals('/some/folder/.rocketeer', $rocketeer);
	}

	public function testCanGetBoundPath()
	{
		$this->swapConfig(array(
			'rocketeer::paths.php' => '/bin/php',
		));
		$path = $this->paths->getPath('php');

		$this->assertEquals('/bin/php', $path);
	}
}
