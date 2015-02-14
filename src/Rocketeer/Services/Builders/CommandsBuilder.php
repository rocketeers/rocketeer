<?php
/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Rocketeer\Services\Builders;

use Rocketeer\Abstracts\AbstractTask;
use Rocketeer\Abstracts\Commands\AbstractCommand;
use Rocketeer\Exceptions\TaskCompositionException;

trait CommandsBuilder
{
    /**
     * Build the command bound to a task
     *
     * @param string|AbstractTask $task
     * @param string|null         $slug
     *
     * @return AbstractCommand
     */
    public function buildCommand($task, $slug = null)
    {
        // Build the task instance
        try {
            $instance = $this->buildTask($task);
        } catch (TaskCompositionException $exception) {
            $instance = null;
        }

        // Get the command name
        $name    = $instance ? $instance->getName() : null;
        $name    = is_string($task) ? $task : $name;
        $command = $this->findQualifiedName($name, 'commands');

        $command = new $command($instance, $slug);
        $command->setLaravel($this->app);

        return $command;
    }
}
