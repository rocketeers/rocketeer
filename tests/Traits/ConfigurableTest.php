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

namespace Rocketeer\Traits;

use Rocketeer\TestCases\RocketeerTestCase;

class ConfigurableTest extends RocketeerTestCase
{
    public function testCanConfigureTask()
    {
        $task = $this->task('Check');
        $this->assertEmpty($task->getOptions());

        $task->setOptions(['foo' => 'bar']);
        $this->assertEquals(['foo' => 'bar'], $task->getOptions());

        $task->configure(['baz' => 'qux']);
        $this->assertEquals(['foo' => 'bar', 'baz' => 'qux'], $task->getOptions());
    }

    public function testRespectsDefaults()
    {
        $task = $this->task('Dependencies');
        $strategy = $this->builder->buildStrategy('Dependencies', 'Composer');

        $this->assertFalse($task->getOption('shared_dependencies', true));
        $this->assertFalse($strategy->getOption('shared_dependencies', true));
    }

    public function testCanGetFlags()
    {
        $expected = ['foo' => ['bar']];

        $task = $this->task('Dependencies');
        $task->setFlags($expected);
        $flags = $task->getFlags();

        $this->assertEquals($expected, $flags);
    }

    public function testCanGetFlagsForCommand()
    {
        $expected = ['--foo' => true];

        $task = $this->task('Dependencies');
        $task->setFlags(['install' => $expected]);
        $flags = $task->getFlags('install');

        $this->assertEquals($expected, $flags);
    }

    public function testDoesntAllowStringFlags()
    {
        $this->setExpectedException('InvalidArgumentException');

        $task = $this->task('Dependencies');
        $task->setFlags(['install' => 'foobar']);
    }
}
