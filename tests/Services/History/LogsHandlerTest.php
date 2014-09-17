<?php
namespace Rocketeer\Services\History;

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
		$logs = $this->logs->getCurrentLogsFile();
		$this->assertEquals($this->server.'/logs/production-.log', $logs);

		$this->connections->setConnection('staging');
		$this->connections->setStage('foobar');
		$logs = $this->logs->getCurrentLogsFile();
		$this->assertEquals($this->server.'/logs/staging-foobar.log', $logs);
	}

	public function testCanLogInformations()
	{
		$this->logs->log('foobar');
		$this->logs->write();
		$logs = $this->logs->getCurrentLogsFile();
		$logs = file_get_contents($logs);

		$this->assertContains('foobar', $logs);
	}

	public function testCanCreateLogsFolderIfItDoesntExistAlready()
	{
		$this->app['path.rocketeer.logs'] = $this->server.'/newlogs';
		$this->logs->log('foobar');
		$this->logs->write();
		$logs = $this->logs->getCurrentLogsFile();

		$this->assertFileExists($logs);
		$this->app['files']->deleteDirectory(dirname($logs));
	}
}
