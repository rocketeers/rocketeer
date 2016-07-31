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

namespace Rocketeer\Services\Builders\Modules;

use Rocketeer\Console\Commands\AbstractCommand;
use Rocketeer\Console\Commands\BaseTaskCommand;
use Rocketeer\Services\Builders\TaskCompositionException;

/**
 * Builds Symfony console commands.
 */
class CommandsBuilder extends AbstractBuilderModule
{
    /**
     * Build the command bound to a task.
     *
     * @param string|\Rocketeer\Tasks\AbstractTask $task
     * @param string|null                          $slug
     *
     * @return \Rocketeer\Console\Commands\AbstractCommand
     */
    public function buildCommand($task, $slug = null)
    {
        // Build the task instance
        try {
            $instance = $this->modulable->buildTask($task);
        } catch (TaskCompositionException $exception) {
            $instance = null;
        }

        // Get the command name
        $name = $instance ? $instance->getName() : null;
        $command = $this->modulable->findQualifiedName($name, 'commands');

        // If no command found, use BaseTaskCommand or task name
        if ($command === BaseTaskCommand::class) {
            $name = is_string($task) ? $task : $name;
            $command = $this->modulable->findQualifiedName($name, 'commands');
        }

        /** @var AbstractCommand $command */
        $command = new $command($instance, $slug);
        $command->setContainer($this->container);

        return $command;
    }

    /**
     * @return string[]
     */
    public function getProvided()
    {
        return [
            'buildCommand',
        ];
    }
}
