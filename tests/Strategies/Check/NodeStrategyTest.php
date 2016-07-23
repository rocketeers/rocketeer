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

class NodeStrategyTest extends RocketeerTestCase
{
    /**
     * @var \Rocketeer\Strategies\Check\PhpStrategy
     */
    protected $strategy;

    public function setUp()
    {
        parent::setUp();

        $this->strategy = $this->builder->buildStrategy('Check', 'Node');
    }

    public function testCanParseLanguageConstraint()
    {
        $this->config->set('strategies.check', 'Npm');
        $this->usesNpm(true, null, [
            'engines' => ['node' => '0.10.30'],
        ]);

        $this->bindDummyConnection('0.8.0');
        $this->assertFalse($this->strategy->language());

        $this->bindDummyConnection('0.11.0');
        $this->assertTrue($this->strategy->language());
    }
}
