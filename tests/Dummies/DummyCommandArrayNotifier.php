<?php

/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Rocketeer\Dummies;

use Rocketeer\Services\TasksHandler;
use Rocketeer\Tasks\Subtasks\Notify;

class DummyCommandArrayNotifier extends DummyBeforeAfterArrayNotifier
{
    /**
     * Register Tasks with Rocketeer.
     *
     * @param \Rocketeer\Services\TasksHandler $queue
     */
    public function onQueue(TasksHandler $queue)
    {
        // Create the task instance
        $notify = new Notify($this->app);
        $notify->setNotifier($this);

        $queue->listenTo('commands.deploy.before', [clone $notify], -10, true);
        $queue->listenTo('commands.deploy.after', [clone $notify], -10, true);
    }
}
