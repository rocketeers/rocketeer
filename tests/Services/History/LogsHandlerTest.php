<?php

/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Rocketeer\Services\History;

use Mockery\MockInterface;
use Rocketeer\Services\Connections\ConnectionsHandler;
use Rocketeer\TestCases\RocketeerTestCase;

class LogsHandlerTest extends RocketeerTestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->app['path.rocketeer.logs'] = $this->server.'/logs';
        $this->swapConfig([
            'logs' => function (ConnectionsHandler $rocketeer) {
                return sprintf('%s-%s.log', $rocketeer->getCurrentConnection()->name, $rocketeer->getCurrentConnection()->stage);
            },
        ]);
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
        $logs = $this->files->read($logs);

        $this->assertContains('foobar', $logs);
    }

    public function testCanCreateLogsFolderIfItDoesntExistAlready()
    {
        $this->app['path.rocketeer.logs'] = $this->server.'/newlogs';
        $this->logs->log('foobar');
        $this->logs->write();
        $logs = $this->logs->getCurrentLogsFile();

        $this->assertVirtualFileExists($logs);
        $this->app['files']->deleteDir(dirname($logs));
    }

    public function testDoesntRecomputeTheLogsFilenameEveryTime()
    {
        $this->expectOutputString('test');

        $this->swapConfig([
            'logs' => function () {
                echo 'test';

                return 'foobar.log';
            },
        ]);

        $this->logs->getCurrentLogsFile();
        $this->logs->getCurrentLogsFile();
    }

    public function testPrependsLogsWithConnectionHandles()
    {
        $this->task()->toHistory('pwd');
        $this->task()->toOutput('Some path');

        $logs    = $this->logs->getFlattenedLogs();
        $matcher = '[{username}@production] $ pwd'.PHP_EOL.'[{username}@production] Some path';

        $this->assertEquals($matcher, $logs);
    }

    public function testLogsMessagesFromExplainerToo()
    {
        $this->task()->toHistory('pwd');
        $this->explainer->success('Getting the current path');

        $logs    = $this->logs->getFlattenedLogs();
        $matcher = '[{username}@production] $ pwd'.PHP_EOL.'[{username}@production] Getting the current path';

        $this->assertEquals($matcher, $logs);
    }

    public function testCanHaveStaticFilenames()
    {
        $this->swapConfig([
            'logs' => 'foobar.txt',
        ]);

        $this->assertEquals($this->server.'/logs/foobar.txt', $this->logs->getCurrentLogsFile());
    }

    public function testDoesntCreateLogsIfInvalidFilename()
    {
        $this->mockFiles(function (MockInterface $mock) {
            return $mock->shouldReceive('put')->with(0)->never();
        });

        $this->swapConfig([
            'logs' => false,
        ]);

        $this->assertFalse($this->logs->getCurrentLogsFile());
        $this->logs->log('foobar');
        $this->logs->write();
    }

    public function testDoesntDuplicateConnectionHandle()
    {
        $this->explainer->server('foobar');

        $this->assertEquals(['[{username}@production] foobar'], $this->logs->getLogs());
    }
}
