<?php
namespace Rocketeer;

use Rocketeer\TestCases\RocketeerTestCase;

class LogsHandlerTest extends RocketeerTestCase
{
	public function setUp()
	{
		parent::setUp();

		$this->app['path.rocketeer.logs'] = $this->server.'/logs';
		$this->swapConfig(array(
			'rocketeer::logs' => function ($rocketeer) {
				return sprintf('%s-%s.log', $rocketeer->getConnection(), $rocketeer->getStage());
			},
		));
	}

	public function testCanGetCurrentLogsFile()
	{
		$logs = $this->app['rocketeer.logs']->getCurrentLogsFile();
		$this->assertEquals($this->server.'/logs/production-.log', $logs);

		$this->app['rocketeer.rocketeer']->setConnection('staging');
		$this->app['rocketeer.rocketeer']->setStage('foobar');
		$logs = $this->app['rocketeer.logs']->getCurrentLogsFile();
		$this->assertEquals($this->server.'/logs/staging-foobar.log', $logs);
	}

	public function testCanLogInformations()
	{
		$this->app['rocketeer.logs']->log('foobar', 'error');
		$logs = $this->app['rocketeer.logs']->getCurrentLogsFile();
		$logs = file_get_contents($logs);

		$this->assertContains('rocketeer.ERROR: foobar [] []', $logs);
	}

	public function testCanLogViaMagicMethods()
	{
		$this->app['rocketeer.logs']->error('foobar');
		$logs = $this->app['rocketeer.logs']->getCurrentLogsFile();
		$logs = file_get_contents($logs);

		$this->assertContains('rocketeer.ERROR: foobar [] []', $logs);
	}

	public function testCanCreateLogsFolderIfItDoesntExistAlready()
	{
		$this->app['path.rocketeer.logs'] = $this->server.'/newlogs';
		$this->app['rocketeer.logs']->error('foobar');
		$logs = $this->app['rocketeer.logs']->getCurrentLogsFile();

		$this->assertFileExists($logs);
		$this->app['files']->deleteDirectory(dirname($logs));
	}
}
