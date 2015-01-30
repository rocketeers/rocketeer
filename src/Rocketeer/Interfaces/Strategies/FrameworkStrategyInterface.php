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
     * Whether Rocketeer is used as a dependency of
     * this application or globally
     *
     * @return boolean
     */
    public function isInsideApplication();

    //////////////////////////////////////////////////////////////////////
    //////////////////////////// CONFIGURATION ///////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * Get the path to export the configuration to
     *
     * @return string
     */
    public function getConfigurationPath();

    /**
     * Get the path to export the plugins configurations to
     *
     * @param string $plugin
     *
     * @return string
     */
    public function getPluginConfigurationPath($plugin);

    //////////////////////////////////////////////////////////////////////
    ////////////////////////////// COMMANDS //////////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * Clear the application's cache
     *
     * @return void
     */
    public function clearCache();

    /**
     * Apply modifiers to some commands before
     * they're executed
     *
     * @param string $command
     *
     * @return string
     */
    public function processCommand($command);

    //////////////////////////////////////////////////////////////////////
    ////////////////////////////// CONSOLE ///////////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * Register a command with the application's CLI
     *
     * @param Command $command
     *
     * @return void
     */
    public function registerConsoleCommand(Command $command);
}
