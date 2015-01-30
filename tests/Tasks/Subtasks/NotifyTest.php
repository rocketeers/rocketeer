<?php
namespace Rocketeer\Tasks\Subtasks;

use Rocketeer\Dummies\DummyBeforeAfterNotifier;
use Rocketeer\Dummies\DummyCommandNotifier;
use Rocketeer\TestCases\RocketeerTestCase;

class NotifyTest extends RocketeerTestCase
{
    public function testDoesntSendTheSameNotificationTwice()
    {
        $this->swapConfig(array(
            'rocketeer::hooks' => array(),
        ));

        $this->tasks->plugin(new DummyBeforeAfterNotifier($this->app));

        $this->expectOutputString('before_deployafter_deployafter_rollback');
        $this->localStorage->set('notifier.name', 'Jean Eude');

        $this->task('Deploy')->fireEvent('before');
        $this->task('Deploy')->fireEvent('after');
        $this->task('Rollback')->fireEvent('after');
    }

    public function testCanProperlyComputeHandleFromCommandEvent()
    {
        $this->swapConfig(array(
            'rocketeer::hooks' => array(),
        ));

        $this->tasks->plugin(new DummyCommandNotifier($this->app));

        $this->expectOutputString('before_deployafter_deploy');
        $this->localStorage->set('notifier.name', 'Jean Eude');

        $this->command('deploy')->fireEvent('before');
        $this->command('deploy')->fireEvent('after');
    }
}
