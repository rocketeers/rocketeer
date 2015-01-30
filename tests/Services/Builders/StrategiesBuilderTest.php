<?php
namespace Rocketeer\Services\Builders;

use Rocketeer\TestCases\RocketeerTestCase;

class StrategiesBuilderTest extends RocketeerTestCase
{
    public function testReturnsNullOnUnbuildableStrategy()
    {
        $built = $this->builder->buildStrategy('Check', '');
        $this->assertInstanceOf('Rocketeer\Strategies\Check\PhpStrategy', $built);

        unset($this->app['rocketeer.strategies.check']);
        $built = $this->builder->buildStrategy('Check', '');
        $this->assertNull($built);
    }
}
