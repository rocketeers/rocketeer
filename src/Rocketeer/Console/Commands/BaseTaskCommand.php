<?php

/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Rocketeer\Console\Commands;

use Rocketeer\Abstracts\AbstractCommand;
use Rocketeer\Abstracts\AbstractTask;

/**
 * A command that wraps around a task class and runs
 * its execute method on fire.
 *
 * @author Maxime Fabre <ehtnam6@gmail.com>
 */
class BaseTaskCommand extends AbstractCommand
{
    /**
     * The default name.
     *
     * @type string
     */
    protected $name = 'deploy:custom';

    /**
     * Build a new custom command.
     *
     * @param AbstractTask|null $task
     * @param string|null       $name A name for the command
     */
    public function __construct(AbstractTask $task = null, $name = null)
    {
        parent::__construct($task);

        // Set name
        if ($this->name === 'deploy:custom' && $task) {
            $this->name = $name ?: $task->getSlug();
            $this->name = 'deploy:'.$this->name;
        }
    }

    /**
     * Fire the custom Task.
     *
     * @return int
     */
    public function fire()
    {
        return $this->fireTasksQueue($this->task->getSlug());
    }
}
