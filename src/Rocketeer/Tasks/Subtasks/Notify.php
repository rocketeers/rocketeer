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

use Illuminate\Support\Arr;
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
     * @type AbstractNotifier
     */
    protected $notifier;

    /**
     * A description of what the task does.
     *
     * @type string
     */
    protected $description = 'Notify a third-party service';

    /**
     * Run the task.
     *
     * @return string|null
     */
    public function execute()
    {
        $hook = str_replace('deploy.', null, $this->event).'_deploy';

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
        $user = $this->localStorage->get('notifier.name');
        if (!$user) {
            $user = $this->command->ask('Who is deploying ?');
            $this->localStorage->set('notifier.name', $user);
        }

        // Get what was deployed
        $branch     = $this->connections->getRepositoryBranch();
        $stage      = $this->connections->getStage();
        $connection = $this->connections->getConnection();
        $server     = $this->connections->getServer();

        // Get hostname
        $credentials = $this->connections->getServerCredentials($connection, $server);
        $host        = Arr::get($credentials, 'host');
        if ($stage) {
            $connection = $stage.'@'.$connection;
        }

        return compact('user', 'branch', 'connection', 'host');
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
        $message = preg_replace('#\{([0-9])\}#', '%$1\$s', $message);
        $message = vsprintf($message, $this->getComponents());

        // Send it
        $this->notifier->send($message);
    }
}
