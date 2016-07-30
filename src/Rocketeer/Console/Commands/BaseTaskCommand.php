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

namespace Rocketeer\Console\Commands;

use Rocketeer\Tasks\AbstractTask;

/**
 * A command that wraps around a task class and runs
 * its execute method on fire.
 */
class BaseTaskCommand extends AbstractCommand
{
    /**
     * The default name.
     *
     * @var string
     */
    protected $name = 'custom';

    /**
     * Build a new custom command.
     *
     * @param \Rocketeer\Tasks\AbstractTask|null $task
     * @param string|null                        $name A name for the command
     */
    public function __construct(AbstractTask $task = null, $name = null)
    {
        parent::__construct($task);

        // Set name
        if ($this->name === 'custom' && $task) {
            $this->setName($name ?: $task->getSlug());
        }
    }

    /**
     * {@inheritdoc}
     */
    public function fire()
    {
        return $this->fireTasksQueue($this->task);
    }
}
