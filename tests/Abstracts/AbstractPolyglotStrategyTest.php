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

namespace Rocketeer\Abstracts;

use Rocketeer\TestCases\RocketeerTestCase;

class AbstractPolyglotStrategyTest extends RocketeerTestCase
{
    public function testDoesntFailPolyglotStrategiesIfOneIsntExecutable()
    {
        $this->expectOutputString('executable');

        /** @var \Rocketeer\Abstracts\Strategies\AbstractPolyglotStrategy $strategy */
        $strategy = $this->builder->buildStrategy('executables', 'Rocketeer\Dummies\ExecutablesPolyglotStrategy');
        $strategy->fire();

        $this->assertTrue($strategy->passed());
    }

    public function testFailsIfOneOfTheStrategiesFails()
    {
        $this->expectOutputString('');

        /** @var \Rocketeer\Abstracts\Strategies\AbstractPolyglotStrategy $strategy */
        $strategy = $this->builder->buildStrategy('failing', 'Rocketeer\Dummies\FailingPolyglotStrategy');
        $result = $strategy->fire();

        $this->assertFalse($result);
        $this->assertFalse($strategy->passed());
    }
}
