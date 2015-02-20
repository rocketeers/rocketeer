<?php
namespace Rocketeer\Plugins;

use Mockery\MockInterface;
use Rocketeer\Dummies\DummyNotifier;
use Rocketeer\Services\Credentials\Keys\ConnectionKey;
use Rocketeer\Services\Credentials\Keys\RepositoryKey;
use Rocketeer\TestCases\RocketeerTestCase;

class AbstractNotifierTest extends RocketeerTestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->swapConfig(array(
            'rocketeer::stages.stages' => array('staging', 'production'),
            'rocketeer::hooks'         => [],
            'rocketeer::connections'   => array(
                'production' => array(
                    'host' => 'foo.bar.com',
                ),
            ),
        ));
        $this->tasks->registerConfiguredEvents();

        $this->notifier = new DummyNotifier($this->app);
        $this->tasks->plugin($this->notifier);
    }

    public function testCanAskForNameIfNoneProvided()
    {
        $this->expectOutputString('foobar finished deploying rocketeers/rocketeer/master on "production/staging" (foo.bar.com)');

        $this->mockCommand([], ['ask' => 'foobar']);
        $this->mock('rocketeer.storage.local', 'LocalStorage', function (MockInterface $mock) {
            return $mock
                ->shouldIgnoreMissing()
                ->shouldReceive('get')->with('connections')
                ->shouldReceive('get')->with('notifier.name')->andReturn(null)
                ->shouldReceive('set')->once()->with('notifier.name', 'foobar');
        });
        $this->mock('rocketeer.connections', 'ConnectionsHandler', function (MockInterface $mock) {
            $handle          = new ConnectionKey(['name' => 'production', 'server' => 0, 'stage' => 'staging']);
            $handle->servers = [['host' => 'foo.bar.com']];

            return $mock
                ->shouldReceive('getCurrentConnection')->andReturn($handle);
        });

        $this->mock('rocketeer.credentials.handler', 'CredentialsHandler', function (MockInterface $mock) {
            return $mock
                ->shouldReceive('getCurrentRepository')->andReturn(new RepositoryKey(['endpoint' => 'rocketeers/rocketeer', 'branch' => 'master']));
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
