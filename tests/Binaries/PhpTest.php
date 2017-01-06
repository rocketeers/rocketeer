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

class PhpTest extends RocketeerTestCase
{
    public function testCanCheckIfUsesHhvm()
    {
        $php = new Php($this->app);
        $hhvm = $php->isHhvm();
        $defined = defined('HHVM_VERSION');

        $this->assertEquals($defined, $hhvm);
    }
}
