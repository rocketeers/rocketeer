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
use Rocketeer\Services\Builders\Modules\StrategiesBuilder;
use Rocketeer\Strategies\AbstractCheckStrategy;
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
        /** @var Builder $prophecy */
        $prophecy = $this->prophesizeBuilder(StrategiesBuilder::class);
        $prophecy->getProvided()->willReturn(['buildStrategy']);
        $prophecy->buildStrategy('Check', 'Node')->willReturn($this->getDummyStrategy('Node', 'language', true));
        $prophecy->buildStrategy('Check', 'Ruby')->willReturn($this->getDummyStrategy('Ruby', 'language', true));
        $prophecy->buildStrategy('Check', 'Php')->willReturn($this->getDummyStrategy('Php', 'language', true));
        $this->builder->register($prophecy->reveal());

        $this->strategy->language();
    }

    public function testCanCheckMissingExtensions()
    {
        /** @var Builder $prophecy */
        $prophecy = $this->prophesizeBuilder(StrategiesBuilder::class);
        $prophecy->getProvided()->willReturn(['buildStrategy']);
        $prophecy->buildStrategy('Check', 'Node')->willReturn($this->getDummyStrategy('Node', 'extensions', ['Node']));
        $prophecy->buildStrategy('Check', 'Ruby')->willReturn($this->getDummyStrategy('Ruby', 'extensions', ['Ruby']));
        $prophecy->buildStrategy('Check', 'Php')->willReturn($this->getDummyStrategy('Php', 'extensions', ['Php']));
        $this->builder->register($prophecy->reveal());

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

        return $prophecy;
    }
}
