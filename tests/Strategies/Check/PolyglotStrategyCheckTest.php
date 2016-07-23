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

use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Rocketeer\Container;
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

        $this->usesBundler(true, null, "ruby '2.0.0'");
        $this->usesNpm(true, null, [
            'engines' => ['node' => '0.10.30'],
        ]);
        $this->usesComposer(true, null, [
            'require' => ['php' => '>=5.6.0', 'ext-sqlite' => '*'],
        ]);

        $this->bindDummyConnection([
            'node --version' => '6.1.1',
            'ruby --version' => '2.1.1',
            'php -r "print PHP_VERSION;"' => '7.1.1',
            'php -m' => 'sqlite',
        ]);

        $this->strategy = $this->builder->buildStrategy('Check', 'Polyglot');
    }

    public function testCanCheckLanguage()
    {
        $this->assertTrue($this->strategy->language());
    }

    public function testCanCheckMissingExtensions()
    {
        $this->assertTrue($this->strategy->language());
    }

    /**
     * Get a dummy strategy.
     *
     * @param string $name
     * @param string $method
     * @param mixed  $result
     *
     * @return ObjectProphecy
     */
    protected function getDummyStrategy($name, $method, $result)
    {
        /** @var AbstractCheckStrategy $prophecy */
        $prophecy = $this->prophesize('Rocketeer\Strategies\Check\\'.$name.'Strategy');
        $prophecy->displayStatus()->willReturn($prophecy);
        $prophecy->isExecutable()->shouldBeCalled()->willReturn(true);
        $prophecy->$method()->shouldBeCalled()->willReturn($result);

        return $prophecy->reveal();
    }

    protected function prophesizeBuilder($builder)
    {
        /** @var Builder $prophecy */
        $prophecy = $this->prophesize($builder);
        $prophecy->setContainer(Argument::type(Container::class))->willReturn();
        $prophecy->setModulable(Argument::type(Builder::class))->willReturn();
        $prophecy->isDefault()->willReturn();

        return $prophecy;
    }
}
