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

namespace Rocketeer\Strategies\Deploy;

/**
 * Describes how an application should be deployed on a server.
 */
interface DeployStrategyInterface
{
    /**
     * Prepare a release and mark it as deployed.
     */
    public function deploy();
}
