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

namespace Rocketeer\Binaries;

use Rocketeer\TestCases\RocketeerTestCase;

class ArtisanTest extends RocketeerTestCase
{
    public function testCanRunMigrations()
    {
        $php = $this->binaries['php'];
        $artisan = new Artisan($this->app);

        $commands = $artisan->migrate();
        $this->assertEquals($php.' artisan migrate --force', $commands);
    }
}
