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

namespace Rocketeer\Strategies\Check;

/**
 * An interface to check if the server is ready to
 * be deployed to.
 */
interface CheckStrategyInterface
{
    /**
     * Check that the PM that'll install
     * the app's dependencies is present.
     *
     * @return bool
     */
    public function manager();

    /**
     * Check that the language used by the
     * application is at the required version.
     *
     * @return bool
     */
    public function language();

    /**
     * Check for the required extensions.
     *
     * @return array
     */
    public function extensions();
}
