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

namespace Rocketeer\Dummies;

use Rocketeer\Services\Tasks\TasksHandler;
use Rocketeer\Tasks\Subtasks\Notify;

class DummyCommandNotifier extends DummyBeforeAfterNotifier
{
    /**
     * Register Tasks with Rocketeer.
     *
     * @param \Rocketeer\Services\Tasks\TasksHandler $tasks
     */
    public function onQueue(TasksHandler $tasks)
    {
        // Create the task instance
        $notify = new Notify($this->container);
        $notify->setNotifier($this);

        $tasks->listenTo('commands.deploy.before', [clone $notify], -10, true);
        $tasks->listenTo('commands.deploy.after', [clone $notify], -10, true);
    }
}
