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

namespace Rocketeer\Strategies\Rollback;

/**
 * Interface for Rollback strategies.
 */
interface RollbackStrategyInterface
{
    /**
     * Rollback to a previous release.
     */
    public function rollback();
}
