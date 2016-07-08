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

namespace Rocketeer\Tasks\Subtasks;

use Rocketeer\Dummies\DummyBeforeAfterNotifier;
use Rocketeer\Dummies\DummyCommandNotifier;
use Rocketeer\TestCases\RocketeerTestCase;

class NotifyTest extends RocketeerTestCase
{
    public function testDoesntSendTheSameNotificationTwice()
    {
        $this->disableTestEvents();
        $this->tasks->plugin(new DummyBeforeAfterNotifier($this->container));

        $this->expectOutputString('before_deployafter_deployafter_rollback');
        $this->localStorage->set('notifier.name', 'Jean Eude');

        $this->task('Deploy')->fireEvent('before');
        $this->task('Deploy')->fireEvent('after');
        $this->task('Rollback')->fireEvent('after');
    }

    public function testCanProperlyComputeHandleFromCommandEvent()
    {
        $this->disableTestEvents();
        $this->tasks->plugin(new DummyCommandNotifier($this->container));

        $this->expectOutputString('before_deployafter_deploy');
        $this->localStorage->set('notifier.name', 'Jean Eude');

        $this->command('deploy')->fireEvent('before');
        $this->command('deploy')->fireEvent('after');
    }
}
