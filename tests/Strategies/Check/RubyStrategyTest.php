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

class RubyStrategyTest extends RocketeerTestCase
{
    /**
     * @var \Rocketeer\Strategies\Check\PhpStrategy
     */
    protected $strategy;

    public function setUp()
    {
        parent::setUp();

        $this->strategy = $this->builder->buildStrategy('Check', 'Ruby');
    }

    public function testCanParseLanguageConstraint()
    {
        $this->usesBundler(true, null, '# Some comments'.PHP_EOL."ruby '2.0.0'");

        $this->bindDummyConnection('1.9.3');
        $this->assertFalse($this->strategy->language());

        $this->bindDummyConnection('5.0.0');
        $this->assertTrue($this->strategy->language());
    }
}
