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

namespace Rocketeer\Services\Builders\Modules;

use Rocketeer\Binaries\AnonymousBinary;
use Rocketeer\Binaries\Vcs\Git;
use Rocketeer\TestCases\RocketeerTestCase;

class BinariesBuilderTest extends RocketeerTestCase
{
    public function testCanBuildBinaries()
    {
        $binary = $this->builder->buildBinary('git');

        $this->assertInstanceOf(Git::class, $binary);
    }

    public function testCanBuildNewBinariesOnDemand()
    {
        $binary = $this->builder->buildBinary('foobar');

        $this->assertInstanceOf(AnonymousBinary::class, $binary);
    }

    public function testExecutesWhichOnAnonymousBinaries()
    {
        $this->localStorage->set('paths.production.foobar', static::$binaries['php']);

        $binary = $this->builder->buildBinary('foobar');
        $this->assertInstanceOf(AnonymousBinary::class, $binary);

        $this->assertEquals(static::$binaries['php'], $binary->getBinary());
    }
}
