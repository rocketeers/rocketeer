<?php
namespace Rocketeer\Services\History;

use Mockery\MockInterface;
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

    public function testDoesntRecomputeTheLogsFilenameEveryTime()
    {
        $this->expectOutputString('test');

        $this->swapConfig(array(
            'rocketeer::logs' => function () {
                echo 'test';

                return 'foobar.log';
            },
        ));

        $this->logs->getCurrentLogsFile();
        $this->logs->getCurrentLogsFile();
    }

    public function testPrependsLogsWithConnectionHandles()
    {
        $this->task()->toHistory('pwd');
        $this->task()->toOutput('Some path');

        $logs    = $this->logs->getFlattenedLogs();
        $matcher = '[anahkiasen@production] $ pwd'.PHP_EOL.'[anahkiasen@production] Some path';

        $this->assertEquals($matcher, $logs);
    }

    public function testLogsMessagesFromExplainerToo()
    {
        $this->task()->toHistory('pwd');
        $this->explainer->success('Getting the current path');

        $logs    = $this->logs->getFlattenedLogs();
        $matcher = '[anahkiasen@production] $ pwd'.PHP_EOL.'[anahkiasen@production] Getting the current path';

        $this->assertEquals($matcher, $logs);
    }

    public function testCanHaveStaticFilenames()
    {
        $this->swapConfig(array(
           'rocketeer::logs' => 'foobar.txt',
        ));

        $this->assertEquals($this->server.'/logs/foobar.txt', $this->logs->getCurrentLogsFile());
    }

    public function testDoesntCreateLogsIfInvalidFilename()
    {
        $this->mockFiles(function (MockInterface $mock) {
           return $mock->shouldReceive('put')->with(0)->never();
        });

        $this->swapConfig(array(
            'rocketeer::logs' => false,
        ));

        $this->assertFalse($this->logs->getCurrentLogsFile());
        $this->logs->log('foobar');
        $this->logs->write();
    }
}
