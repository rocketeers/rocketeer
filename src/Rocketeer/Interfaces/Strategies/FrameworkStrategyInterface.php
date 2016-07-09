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

namespace Rocketeer\Interfaces\Strategies;

interface FrameworkStrategyInterface
{
    /**
     * Get the name of the framework.
     *
     * @return string
     */
    public function getName();

    //////////////////////////////////////////////////////////////////////
    //////////////////////////// CONFIGURATION ///////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * Get the path to export the configuration to.
     *
     * @return string
     */
    public function getConfigurationPath();

    /**
     * Get the path to export the plugins configurations to.
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
     * Clear the application's cache.
     */
    public function clearCache();

    /**
     * Apply modifiers to some commands before
     * they're executed.
     *
     * @param string $command
     *
     * @return string
     */
    public function processCommand($command);
}
