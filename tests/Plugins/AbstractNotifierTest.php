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

namespace Rocketeer\Plugins;

use Mockery\MockInterface;
use Rocketeer\Dummies\DummyNotifier;
use Rocketeer\Services\Connections\ConnectionsHandler;
use Rocketeer\Services\Connections\Credentials\CredentialsHandler;
use Rocketeer\Services\Connections\Credentials\Keys\ConnectionKey;
use Rocketeer\Services\Connections\Credentials\Keys\RepositoryKey;
use Rocketeer\TestCases\RocketeerTestCase;

class AbstractNotifierTest extends RocketeerTestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->swapConfig([
            'stages.stages' => ['staging', 'production'],
            'hooks' => [],
            'connections' => [
                'production' => [
                    'host' => 'foo.bar.com',
                ],
            ],
        ]);

        $this->tasks->registerConfiguredEvents();

        $this->notifier = new DummyNotifier($this->container);
        $this->tasks->plugin($this->notifier);
    }

    public function testCanAskForNameIfNoneProvided()
    {
        $this->expectOutputString('foobar finished deploying rocketeers/rocketeer/master on "production/staging" (foo.bar.com)');

        $this->mockCommand([], ['ask' => 'foobar']);
        $this->mock('storage.local', 'Storage', function (MockInterface $mock) {
            return $mock
                ->shouldIgnoreMissing()
                ->shouldReceive('get')->with('connections')
                ->shouldReceive('get')->with('notifier.name')->andReturn(null)
                ->shouldReceive('set')->once()->with('notifier.name', 'foobar');
        });
        $this->mock(ConnectionsHandler::class, ConnectionsHandler::class, function (MockInterface $mock) {
            $handle = new ConnectionKey(['name' => 'production', 'server' => 0, 'stage' => 'staging']);
            $handle->servers = [['host' => 'foo.bar.com']];

            return $mock
                ->shouldReceive('getCurrentConnectionKey')->andReturn($handle);
        });

        $this->mock(CredentialsHandler::class, CredentialsHandler::class, function (MockInterface $mock) {
            return $mock
                ->shouldReceive('getCurrentRepository')->andReturn(new RepositoryKey([
                    'endpoint' => 'rocketeers/rocketeer',
                    'branch' => 'master',
                ]));
        });

        $this->task('deploy')->fireEvent('before');
    }

    public function testCanAppendStageToDetails()
    {
        $this->expectOutputString('Jean Eude finished deploying Anahkiasen/html-object/master on "production/staging" (foo.bar.com)');
        $this->localStorage->set('notifier.name', 'Jean Eude');
        $this->tasks->registerConfiguredEvents();
        $this->connections->setStage('staging');

        $this->task('Deploy')->fireEvent('before');
    }

    public function testCanSendDeploymentsNotifications()
    {
        $this->expectOutputString('Jean Eude finished deploying Anahkiasen/html-object/master on "production" (foo.bar.com)');
        $this->localStorage->set('notifier.name', 'Jean Eude');

        $this->task('Deploy')->fireEvent('after');
    }

    public function testCanSendRollbackNotifications()
    {
        $this->expectOutputString('Jean Eude rolled back Anahkiasen/html-object/master on "production" to previous version (foo.bar.com)');
        $this->localStorage->set('notifier.name', 'Jean Eude');

        $this->task('Rollback')->fireEvent('after');
    }

    public function testDoesntSendNotificationsInPretendMode()
    {
        $this->expectOutputString('');
        $this->localStorage->set('notifier.name', 'Jean Eude');

        $this->pretendTask('Deploy')->fireEvent('after');
    }
}
