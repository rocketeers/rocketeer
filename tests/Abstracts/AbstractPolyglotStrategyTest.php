<?php
namespace Rocketeer\Abstracts;

use Rocketeer\TestCases\RocketeerTestCase;

class AbstractPolyglotStrategyTest extends RocketeerTestCase
{
    public function testDoesntFailPolyglotStrategiesIfOneIsntExecutable()
    {
        $this->expectOutputString('executable');

        /** @type \Rocketeer\Abstracts\Strategies\AbstractPolyglotStrategy $strategy */
        $strategy = $this->builder->buildStrategy('executables', 'Rocketeer\Dummies\ExecutablesPolyglotStrategy');
        $strategy->fire();

        $this->assertTrue($strategy->passed());
    }

    public function testFailsIfOneOfTheStrategiesFails()
    {
        $this->expectOutputString('');

        /** @type \Rocketeer\Abstracts\Strategies\AbstractPolyglotStrategy $strategy */
        $strategy = $this->builder->buildStrategy('failing', 'Rocketeer\Dummies\FailingPolyglotStrategy');
        $strategy->fire();

        $this->assertFalse($strategy->passed());
    }
}
