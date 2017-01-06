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
use Rocketeer\TestCases\RocketeerTestCase;

class NotifyTest extends RocketeerTestCase
{
    public function testDoesntSendTheSameNotificationTwice()
    {
        $this->swapConfig([
            'rocketeer::hooks' => [],
        ]);

        $this->tasks->plugin(new DummyBeforeAfterNotifier($this->app));

        $this->expectOutputString('before_deployafter_deploy');
        $this->localStorage->set('notifier.name', 'Jean Eude');

        $this->task('Deploy')->fireEvent('before');
        $this->task('Deploy')->fireEvent('after');
    }
}
