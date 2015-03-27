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
 * Interface for the various migration strategies.
 *
 * @author Maxime Fabre <ehtnam6@gmail.com>
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
