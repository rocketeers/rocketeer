<?php

/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Rocketeer\Interfaces\Strategies;

/**
 * Interface for the various deployment strategies.
 *
 * @author Maxime Fabre <ehtnam6@gmail.com>
 */
interface DeployStrategyInterface
{
    /**
     * Deploy a new clean copy of the application.
     *
     * @param string|null $destination
     *
     * @return bool
     */
    public function deploy($destination = null);

    /**
     * Update the latest version of the application.
     *
     * @param bool $reset
     *
     * @return bool
     */
    public function update($reset = true);
}
