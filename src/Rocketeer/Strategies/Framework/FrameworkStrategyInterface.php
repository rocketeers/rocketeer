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

namespace Rocketeer\Strategies\Framework;

/**
 * Interface for Rocketeer to integrate with a framework.
 */
interface FrameworkStrategyInterface
{
    /**
     * Apply modifiers to some commands before
     * they're executed.
     *
     * @param string $command
     *
     * @return string
     */
    public function processCommand($command);

    /**
     * Clear the application's cache.
     */
    public function clearCache();
}
