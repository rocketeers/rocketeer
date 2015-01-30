<?php
namespace Rocketeer\Strategies\Frameworks;

use Illuminate\Support\Str;
use Rocketeer\Abstracts\Strategies\AbstractStrategy;
use Rocketeer\Interfaces\Strategies\FrameworkStrategyInterface;
use Symfony\Component\Console\Command\Command;

class LaravelStrategy extends AbstractStrategy implements FrameworkStrategyInterface
{
    /**
     * Get the name of the framework
     *
     * @return string
     */
    public function getName()
    {
        return 'laravel';
    }

    /**
     * Clear the application's cache
     *
     * @return void
     */
    public function clearCache()
    {
        $this->artisan()->runForCurrentRelease('clearCache');
    }

    /**
     * Register a command with the application's CLI
     *
     * @param Command $command
     *
     * @return void
     */
    public function registerConsoleCommand(Command $command)
    {
        $this->app['artisan']->add($command);
    }

    /**
     * Get the path to export the configuration to
     *
     * @return string
     */
    public function EtgetConfigurationPath()
    {
        return $this->app['path'].'/config/packages/anahkiasen/rocketeer';
    }

    /**
     * Get the path to export the configuration to
     *
     * @return string
     */
    public function getConfigurationPath()
    {
        // TODO: Implement getConfigurationPath() method.
    }

    /**
     * Apply modifiers to some commands before
     * they're executed
     *
     * @param string $command
     *
     * @return string
     */
    public function processCommand($command)
    {
        // Add environment flag to commands
        $stage = $this->connections->getStage();
        if (Str::contains($command, 'artisan') && $stage) {
            $command .= ' --env="'.$stage.'"';
        }

        return $command;
    }
}
