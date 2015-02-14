<?php
namespace Rocketeer\TestCases\Modules;

use Illuminate\Console\Command;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Tester\CommandTester;

trait Building
{
    /**
     * Get and execute a command
     *
     * @param Command|string|null $command
     * @param array               $arguments
     * @param array               $options
     *
     * @return CommandTester
     */
    protected function executeCommand($command = null, $arguments = [], $options = [])
    {
        $command = $this->command($command);

        // Execute
        $tester = new CommandTester($command);
        $tester->execute($arguments, $options);

        return $tester;
    }

    /**
     * Get a pretend AbstractTask to run bogus commands
     *
     * @param string $task
     * @param array  $options
     * @param array  $expectations
     *
     * @return \Rocketeer\Abstracts\AbstractTask
     */
    protected function pretendTask($task = 'Deploy', $options = array(), array $expectations = array())
    {
        $this->pretend($options, $expectations);

        return $this->task($task);
    }

    /**
     * Get AbstractTask instance
     *
     * @param string $task
     * @param array  $options
     *
     * @return \Rocketeer\Abstracts\AbstractTask
     */
    protected function task($task = null, $options = array())
    {
        if ($options) {
            $this->mockCommand($options);
        }

        if (!$task) {
            return $this->task;
        }

        return $this->builder->buildTask($task);
    }

    /**
     * @param $command
     *
     * @return Command
     */
    protected function command($command)
    {
        // Fetch command from Container if necessary
        if (!$command instanceof Command) {
            $command = $command ? '.'.$command : null;
            $command = $this->app['rocketeer.commands'.$command];
        } elseif (!$command->getLaravel()) {
            $command->setLaravel($this->app);
            $command->setHelperSet(new HelperSet(['question' => new QuestionHelper()]));
        }

        return $command;
    }
}
