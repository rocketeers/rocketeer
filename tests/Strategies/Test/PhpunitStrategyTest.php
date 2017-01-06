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

namespace Rocketeer\Strategies\Test;

use Rocketeer\TestCases\RocketeerTestCase;

class PhpunitStrategyTest extends RocketeerTestCase
{
    public function testCanRunTests()
    {
        $this->pretendTask();
        $this->builder->buildStrategy('Test', 'Phpunit')->test();

        $this->assertHistory([
            [
                'cd {server}/releases/20000000000000',
                '{phpunit} --stop-on-failure',
            ],
        ]);
    }
}
