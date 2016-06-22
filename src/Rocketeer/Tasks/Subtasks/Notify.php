<?php

/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Rocketeer\Tasks\Subtasks;

use Rocketeer\Abstracts\AbstractTask;
use Rocketeer\Plugins\AbstractNotifier;

/**
 * Notify a third-party service.
 *
 * @author Maxime Fabre <ehtnam6@gmail.com>
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
     * Get the message's components.
     *
     * @return string[]
     */
    protected function getComponents()
    {
        // Get user name
        $user = $this->command->option('user');
        if (!$user) {
            $user = $_SERVER['USER'];
            if (!$user) {
                $this->explainer->line('The user name is required. --user or $USER environment variables.');
                $this->halt();
            }
        }

        // Get what was deployed
        $repository = $this->credentials->getCurrentRepository();
        $connection = $this->connections->getCurrentConnection();

        return [
            'user' => $user,
            'branch' => $repository->branch,
            'handle' => $connection->toHandle(),
            'host' => $connection->host,
            'repository' => $repository->getName(),
        ];
    }

    /**
     * Prepare and send a message.
     *
     * @param string $message
     */
    public function prepareThenSend($message)
    {
        // Don't send a notification if pretending to deploy
        if ($this->command->option('pretend')) {
            return;
        }

        // Build message
        $message = $this->notifier->getMessageFormat($message);
        if (!$message) {
            return;
        }

        if (is_array($message)) {
            $components = $this->getComponents();
            foreach ($message as $key => $value) {
                $message[$key] = $this->_formatMessage($message[$key], $components);
            }
        } else {
            $message = $this->_formatMessage($message);
        }

        // Send it
        $this->notifier->send($message);
    }


    /**
     * Format message
     *
     * @param  string
     * @param  array|false
     *
     * @return string
     */
    private function _formatMessage($message, $components = false)
    {
        if (!$components) {
            $components = $this->getComponents();
        }
        $message = preg_replace('#\{([0-9])\}#', '%$1\$s', $message);
        $message = vsprintf($message, $components);
        return $message;
    }
}
