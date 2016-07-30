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

namespace Rocketeer\Services\Builders;

use Exception;

/**
 * Exception for when a task (of any kind) could not
 * successfully be built by the TasksBuilder.
 */
class TaskCompositionException extends Exception
{
    /**
     * @param string $task
     */
    public function __construct($task)
    {
        $task = is_object($task) ? get_class($task) : $task;

        parent::__construct('Unable to build task: '.$task);
    }
}
