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
        $this->usesComposer(true, null, [
            'require' => ['php' => '>=5.6.0'],
        ]);

        $this->bindDummyConnection('7.0.0');
        $this->assertTrue($this->strategy->language());

        $this->bindDummyConnection('3.4');
        $this->assertFalse($this->strategy->language());
    }

    public function testCanCheckPhpExtensions()
    {
        $this->usesComposer(true, null, [
           'required' => ['ext-sqlite' => '*'],
        ]);

        $this->mockHhvm(false, [
            'which composer' => 'composer',
            'php -m' => 'sqlite',
        ]);

        $this->assertTrue($this->strategy->extensions());
    }

    public function testCanCheckForHhvmExtensions()
    {
        $this->mockHhvm();
        $exists = $this->strategy->checkExtension('_hhvm');

        $this->assertTrue($exists);
    }
}
