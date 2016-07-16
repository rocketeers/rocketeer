<?php

/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace Rocketeer\Services\History;

use League\Flysystem\Filesystem;
use Rocketeer\Services\Connections\ConnectionsHandler;
use Rocketeer\TestCases\RocketeerTestCase;

class LogsHandlerTest extends RocketeerTestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->container->add('path.rocketeer.logs', $this->server.'/logs');
        $this->swapConfig([
            'logs' => function (ConnectionsHandler $rocketeer) {
                return sprintf('%s-%s.log', $rocketeer->getCurrentConnectionKey()->name, $rocketeer->getCurrentConnectionKey()->stage);
            },
        ]);
    }

    public function testCanGetCurrentLogsFile()
    {
        $logs = $this->logs->getLogsRealpath();
        $this->assertEquals($this->server.'/logs/production-.log', $logs);

        $this->connections->setCurrentConnection('staging');
        $this->connections->setStage('foobar');
        $logs = $this->logs->getLogsRealpath();
        $this->assertEquals($this->server.'/logs/staging-foobar.log', $logs);
    }

    public function testCanLogInformations()
    {
        $this->logs->log('foobar');
        $realpath = $this->logs->getLogsRealpath();
        $logs = $this->files->read($realpath);

        $this->assertContains('foobar', $logs);
    }

    public function testCanCreateLogsFolderIfItDoesntExistAlready()
    {
        $this->container->add('path.rocketeer.logs', $this->server.'/newlogs');
        $this->logs->log('foobar');
        $logs = $this->logs->getLogsRealpath();

        $this->assertVirtualFileExists($logs);
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

        $this->logs->log('foo');
        $this->logs->log('foo');
    }

    public function testPrependsLogsWithConnectionHandles()
    {
        $this->task()->toHistory('pwd');
        $this->task()->toOutput('Some path');

        $logs = $this->logs->getFlattenedLogs();

        $this->assertContains('{username}@production:$ $ pwd', $logs);
        $this->assertContains('{username}@production:$ Some path', $logs);
    }

    public function testLogsMessagesFromExplainerToo()
    {
        $this->task()->toHistory('pwd');
        $this->explainer->success('Getting the current path');

        $logs = $this->logs->getFlattenedLogs();

        $this->assertContains('{username}@production:$ $ pwd', $logs);
        $this->assertContains('{username}@production:$ Getting the current path', $logs);
    }

    public function testCanHaveStaticFilenames()
    {
        $this->swapConfig([
            'logs' => 'foobar.txt',
        ]);

        $this->assertEquals($this->server.'/logs/foobar.txt', $this->logs->getLogsRealpath());
    }

    public function testDoesntCreateLogsIfInvalidFilename()
    {
        $prophecy = $this->bindProphecy(Filesystem::class);

        $this->swapConfig([
            'logs' => false,
        ]);

        $this->assertFalse($this->logs->getLogsRealpath());
        $this->logs->log('foobar');

        $prophecy->put()->shouldNotHaveBeenCalled();
    }

    public function testDoesntDuplicateConnectionHandle()
    {
        $this->explainer->server('foobar');
        $logs = $this->logs->getLogs();

        $this->assertContains('{username}@production:$ foobar', $logs[0]);
    }
}
