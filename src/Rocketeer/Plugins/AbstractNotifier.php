<?php

/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Rocketeer\Plugins;

use Rocketeer\Abstracts\AbstractPlugin;
use Rocketeer\Services\TasksHandler;
use Rocketeer\Tasks\Subtasks\Notify;

/**
 * A base class for notification services to extends.
 */
abstract class AbstractNotifier extends AbstractPlugin
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

        $queue->addTaskListeners('deploy', 'before', [clone $notify], -10, true);
        $queue->addTaskListeners('deploy', 'after', [clone $notify], -10, true);
    }

    /**
     * Send a given message.
     *
     * @param string $message
     */
    abstract public function send($message);

    /**
     * Get the default message format.
     *
     * @param string $message The message handle
     *
     * @return string
     */
    abstract public function getMessageFormat($message);
}
