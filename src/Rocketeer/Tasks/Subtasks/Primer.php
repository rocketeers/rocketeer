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

/**
 * Executes some sanity-check commands before deploy.
 *
 * @author Maxime Fabre <ehtnam6@gmail.com>
 */
class Primer extends AbstractTask
{
    /**
     * A description of what the task does.
     *
     * @type string
     */
    protected $description = 'Run local checks to ensure deploy can proceed';

    /**
     * Whether to run the commands locally
     * or on the server.
     *
     * @type bool
     */
    protected $local = true;

    /**
     * Whether the task needs to be run on each stage or globally.
     *
     * @type bool
     */
    public $usesStages = false;

    /**
     * Run the task.
     *
     * @return bool
     */
    public function execute()
    {
        $tasks = $this->getHookedTasks('primer', [$this]);
        if (!$tasks) {
            return true;
        }

        $this->run($tasks);

        return $this->status();
    }
}
