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

namespace Rocketeer\Strategies\Check;

use Rocketeer\TestCases\RocketeerTestCase;

class PhpStrategyTest extends RocketeerTestCase
{
    /**
     * @var \Rocketeer\Strategies\Check\PhpStrategy
     */
    protected $strategy;

    public function setUp()
    {
        parent::setUp();

        $this->strategy = $this->builder->buildStrategy('Check', 'Php');
    }

    public function testCanCheckPhpVersion()
    {
        $version = $this->bash->php()->run('version');

        $this->mockFiles(function ($mock) use ($version) {
            return $mock
                ->shouldReceive('put')
                ->shouldReceive('glob')->andReturn([])
                ->shouldReceive('exists')->andReturn(true)
                ->shouldReceive('get')->andReturn('{"require":{"php":">='.$version.'"}}');
        });

        $this->assertTrue($this->strategy->language());

        // This is is going to come bite me in the ass in 10 years
        $this->mockFiles(function ($mock) {
            return $mock
                ->shouldReceive('put')
                ->shouldReceive('glob')->andReturn([])
                ->shouldReceive('exists')->andReturn(true)
                ->shouldReceive('get')->andReturn('{"require":{"php":">=999.9.0"}}');
        });

        $this->assertFalse($this->strategy->language());
    }

    public function testCanCheckPhpExtensions()
    {
        $this->swapConfig([
            'database.default' => 'sqlite',
            'cache.driver' => 'redis',
            'session.driver' => 'apc',
        ]);

        $this->strategy->extensions();

        $this->assertHistory(['{php} -m']);
    }

    public function testCanCheckForHhvmExtensions()
    {
        $this->mockRemote('1');
        $exists = $this->strategy->checkPhpExtension('_hhvm');

        $this->assertTrue($exists);
    }
}
