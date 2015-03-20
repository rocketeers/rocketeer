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

use Mockery\MockInterface;
use Rocketeer\TestCases\RocketeerTestCase;

class PhpStrategyTest extends RocketeerTestCase
{
    /**
     * @type \Rocketeer\Strategies\Check\PhpStrategy
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

        $this->mockFiles(function (MockInterface $mock) use ($version) {
            return $mock
                ->shouldReceive('put')
                ->shouldReceive('glob')->andReturn([])
                ->shouldReceive('has')->andReturn(true)
                ->shouldReceive('read')->andReturn('{"require":{"php":">='.$version.'"}}');
        });
        $this->assertTrue($this->strategy->language());

        // This is is going to come bite me in the ass in 10 years
        $this->mockFiles(function (MockInterface $mock) {
            return $mock
                ->shouldReceive('put')
                ->shouldReceive('glob')->andReturn([])
                ->shouldReceive('has')->andReturn(true)
                ->shouldReceive('read')->andReturn('{"require":{"php":">=12.9.0"}}');
        });
        $this->assertFalse($this->strategy->language());
    }

    public function testCanCheckPhpExtensions()
    {
        $this->swapConfig([
            'database.default' => 'sqlite',
            'cache.driver'     => 'redis',
            'session.driver'   => 'apc',
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
