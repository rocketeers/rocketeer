<?php
namespace Rocketeer\Interfaces\Strategies;

use Symfony\Component\Console\Command\Command;

interface FrameworkStrategyInterface
{
    /**
     * Get the name of the framework
     *
     * @return string
     */
    public function getName();

    /**
     * Clear the application's cache
     *
     * @return void
     */
    public function clearCache();

    /**
     * Register a command with the application's CLI
     *
     * @param Command $command
     *
     * @return void
     */
    public function registerCommand(Command $command);
}
