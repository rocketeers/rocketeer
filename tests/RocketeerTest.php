<?php
namespace Rocketeer;

use Rocketeer\TestCases\RocketeerTestCase;

class RocketeerTest extends RocketeerTestCase
{
	public function testCanGetApplicationName()
	{
		$this->assertEquals('foobar', $this->rocketeer->getApplicationName());
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
