<?php

/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Rocketeer\Strategies\Migrate;

use Rocketeer\Abstracts\Strategies\AbstractStrategy;
use Rocketeer\Interfaces\Strategies\MigrateStrategyInterface;

class ArtisanStrategy extends AbstractStrategy implements MigrateStrategyInterface
{
    /**
     * @type string
     */
    protected $description = 'Migrates your database with Laravel\'s Artisan CLI';

    /**
     * Whether this particular strategy is runnable or not.
     *
     * @return bool
     */
    public function isExecutable()
    {
        return (bool) $this->artisan()->getBinary();
    }

    /**
     * Run outstanding migrations.
     *
     * @return bool|null
     */
    public function migrate()
    {
        return $this->artisan()->runForCurrentRelease('migrate');
    }

    /**
     * Seed the database.
     *
     * @return bool|null
     */
    public function seed()
    {
        return $this->artisan()->runForCurrentRelease('seed');
    }
}
