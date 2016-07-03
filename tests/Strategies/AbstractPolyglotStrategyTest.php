<?php

/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Rocketeer\Strategies;

use Rocketeer\Dummies\ExecutablesPolyglotStrategy;
use Rocketeer\Dummies\FailingPolyglotStrategy;
use Rocketeer\TestCases\RocketeerTestCase;

class AbstractPolyglotStrategyTest extends RocketeerTestCase
{
    public function testDoesntFailPolyglotStrategiesIfOneIsntExecutable()
    {
        $this->expectOutputString('executable-bar');

        /** @var \Rocketeer\Strategies\AbstractPolyglotStrategy $strategy */
        $strategy = $this->builder->buildStrategy('executables', ExecutablesPolyglotStrategy::class);
        $strategy->fire();

        $this->assertTrue($strategy->passed());
    }

    public function testFailsIfOneOfTheStrategiesFails()
    {
        $this->expectOutputString('');

        /** @var \Rocketeer\Strategies\AbstractPolyglotStrategy $strategy */
        $strategy = $this->builder->buildStrategy('failing', FailingPolyglotStrategy::class);
        $result = $strategy->fire();

        $this->assertFalse($result);

        $this->assertFalse($strategy->passed());
    }
}
