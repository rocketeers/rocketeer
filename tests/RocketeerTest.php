<?php
namespace Rocketeer;

use Rocketeer\TestCases\RocketeerTestCase;

class RocketeerTest extends RocketeerTestCase
{
	////////////////////////////////////////////////////////////////////
	//////////////////////////////// TESTS /////////////////////////////
	////////////////////////////////////////////////////////////////////

	public function testCanGetApplicationName()
	{
		$this->assertEquals('foobar', $this->rocketeer->getApplicationName());
	}

	public function testCanGetHomeFolder()
	{
		$this->assertEquals($this->server, $this->rocketeer->getHomeFolder());
	}

	public function testCanGetFolderWithStage()
	{
		$this->connections->setStage('test');

		$this->assertEquals($this->server.'/test/current', $this->rocketeer->getFolder('current'));
	}

	public function testCanGetAnyFolder()
	{
		$this->assertEquals($this->server.'/current', $this->rocketeer->getFolder('current'));
	}

	public function testCanReplacePatternsInFolders()
	{
		$folder = $this->rocketeer->getFolder('{path.storage}');

		$this->assertEquals($this->server.'/app/storage', $folder);
	}

	public function testCannotReplaceUnexistingPatternsInFolders()
	{
		$folder = $this->rocketeer->getFolder('{path.foobar}');

		$this->assertEquals($this->server.'/', $folder);
	}

	public function testCanUseRecursiveStageConfiguration()
	{
		$this->swapConfig(array(
			'rocketeer::scm.branch'                   => 'master',
			'rocketeer::on.stages.staging.scm.branch' => 'staging',
		));

		$this->assertOptionValueEquals('master', 'scm.branch');
		$this->connections->setStage('staging');
		$this->assertOptionValueEquals('staging', 'scm.branch');
	}

	public function testCanUseRecursiveConnectionConfiguration()
	{
		$this->swapConfig(array(
			'rocketeer::default'                           => 'production',
			'rocketeer::scm.branch'                        => 'master',
			'rocketeer::on.connections.staging.scm.branch' => 'staging',
		));
		$this->assertOptionValueEquals('master', 'scm.branch');

		$this->swapConfig(array(
			'rocketeer::default'                           => 'staging',
			'rocketeer::scm.branch'                        => 'master',
			'rocketeer::on.connections.staging.scm.branch' => 'staging',
		));
		$this->assertOptionValueEquals('staging', 'scm.branch');
	}

	public function testRocketeerCanGuessWhichStageHesIn()
	{
		$path  = '/home/www/foobar/production/releases/12345678901234/app';
		$stage = Rocketeer::getDetectedStage('foobar', $path);
		$this->assertEquals('production', $stage);

		$path  = '/home/www/foobar/staging/releases/12345678901234/app';
		$stage = Rocketeer::getDetectedStage('foobar', $path);
		$this->assertEquals('staging', $stage);

		$path  = '/home/www/foobar/releases/12345678901234/app';
		$stage = Rocketeer::getDetectedStage('foobar', $path);
		$this->assertEquals(false, $stage);
	}
}
