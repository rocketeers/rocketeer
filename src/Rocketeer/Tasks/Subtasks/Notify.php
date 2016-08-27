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

use Rocketeer\Plugins\AbstractNotifier;
use Rocketeer\Tasks\AbstractTask;

/**
 * Notify a third-party service.
 */
class Notify extends AbstractTask
{
    /**
     * The message format.
     *
     * @var AbstractNotifier
     */
    protected $notifier;

    /**
     * A description of what the task does.
     *
     * @var string
     */
    protected $description = 'Notify a third-party service';

    /**
     * Run the task.
     *
     * @return string|null
     */
    public function execute()
    {
        $hook = preg_replace('/rocketeer\.(commands|tasks)\.(.+)/', '$2', $this->event->getName());
        $hook = explode('.', $hook);
        $hook = $hook[1].'_'.$hook[0];

        $this->prepareThenSend($hook);
    }

    /**
     * @param AbstractNotifier $notifier
     */
    public function setNotifier($notifier)
    {
        $this->notifier = $notifier;
    }

    ////////////////////////////////////////////////////////////////////
    /////////////////////////////// MESSAGE ////////////////////////////
    ////////////////////////////////////////////////////////////////////

    /**
     * @param string $type
     */
    public function prepareThenSend($type)
    {
        // Don't send a notification if pretending to deploy
        if ($this->command->option('pretend')) {
            return;
        }

        // Build message
        $message = $this->notifier->getMessageFormat($type);
        $message = preg_replace('#\{([0-9])\}#', '%$1\$s', $message);
        $message = vsprintf($message, $this->getComponents());

        // Send it
        $this->notifier->send($message, $type);
    }

    /**
     * Get the message's components.
     *
     * @return string[]
     */
    protected function getComponents()
    {
        // Get user name
        $user = $this->localStorage->get('notifier.name');
        if (!$user) {
            $user = $this->command->ask('Who is deploying ?') ?: 'Someone';
            $this->localStorage->set('notifier.name', $user);
        }

        // Get what was deployed
        $repository = $this->credentials->getCurrentRepository();
        $connection = $this->connections->getCurrentConnectionKey();

        return [
            'user' => $user,
            'branch' => $repository->branch,
            'handle' => $connection->toHandle(),
            'host' => $connection->host,
            'repository' => $repository->getName(),
        ];
    }
}
