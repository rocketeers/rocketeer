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

use Rocketeer\Binaries\PackageManagers\Composer;
use Rocketeer\TestCases\RocketeerTestCase;

class ComposerTest extends RocketeerTestCase
{
    public function testCanWrapWithPhpIfArchive()
    {
        $composer = new Composer($this->app);
        $composer->setBinary('composer.phar');

        $this->assertEquals($this->binaries['php'].' composer.phar install', $composer->install());
    }
}
