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

namespace Rocketeer\Strategies\Migrate;

/**
 * Interface for the various migration strategies.
 */
interface MigrateStrategyInterface
{
    /**
     * Run outstanding migrations.
     *
     * @return bool
     */
    public function migrate();

    /**
     * Seed the database.
     *
     * @return bool
     */
    public function seed();
}
