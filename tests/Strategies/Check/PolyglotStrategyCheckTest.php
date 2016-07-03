<?php

/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Rocketeer\Strategies\Check;

use Mockery;
use Mockery\MockInterface;
use Rocketeer\Services\Builders\Builder;
use Rocketeer\TestCases\RocketeerTestCase;

class PolyglotStrategyCheckTest extends RocketeerTestCase
{
    /**
     * @var \Rocketeer\Strategies\Check\PolyglotStrategy
     */
    protected $strategy;

    public function setUp()
    {
        parent::setUp();

        $this->strategy = $this->builder->buildStrategy('Check', 'Polyglot');
    }

    public function testCanCheckLanguage()
    {
        $this->mock(Builder::class, Builder::class, function (MockInterface $mock) {
            return $mock
                ->shouldReceive('buildStrategy')->with('Check', 'Node')->andReturn($this->getDummyStrategy('Node', 'language', true))
                ->shouldReceive('buildStrategy')->with('Check', 'Ruby')->andReturn($this->getDummyStrategy('Ruby', 'language', true))
                ->shouldReceive('buildStrategy')->with('Check', 'Php')->andReturn($this->getDummyStrategy('Php', 'language', true));
        });

        $this->strategy->language();
    }

    public function testCanCheckMissingExtensions()
    {
        $this->mock(Builder::class, Builder::class, function (MockInterface $mock) {
            return $mock
                ->shouldReceive('buildStrategy')->with('Check', 'Node')->andReturn($this->getDummyStrategy('Node', 'extensions', ['Node']))
                ->shouldReceive('buildStrategy')->with('Check', 'Ruby')->andReturn($this->getDummyStrategy('Ruby', 'extensions', ['Ruby']))
                ->shouldReceive('buildStrategy')->with('Check', 'Php')->andReturn($this->getDummyStrategy('Php', 'extensions', ['Php']));
        });

        $extensions = $this->strategy->extensions();
        $this->assertEquals(['Node', 'Php', 'Ruby'], $extensions);
    }

    /**
     * Get a dummy strategy.
     *
     * @param string $name
     * @param string $method
     * @param mixed  $result
     *
     * @return mixed
     */
    protected function getDummyStrategy($name, $method, $result)
    {
        return Mockery::mock('Rocketeer\Strategies\Check\\'.$name.'Strategy')
                      ->shouldIgnoreMissing()
                      ->shouldReceive('displayStatus')->andReturnSelf()
                      ->shouldReceive('isExecutable')->once()->andReturn(true)
                      ->shouldReceive($method)->once()->andReturn($result)
                      ->mock();
    }
}
