<?php
namespace Rocketeer\Dummies;

use Rocketeer\Services\TasksHandler;
use Rocketeer\Tasks\Subtasks\Notify;

class DummyCommandNotifier extends DummyBeforeAfterNotifier
{
    /**
     * Register Tasks with Rocketeer
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
