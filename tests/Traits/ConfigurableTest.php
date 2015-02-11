<?php
namespace Rocketeer\Traits;

use Rocketeer\TestCases\RocketeerTestCase;

class ConfigurableTest extends RocketeerTestCase
{
    public function testCanConfigureTask()
    {
        $task = $this->task('Deploy');
        $this->assertEmpty($task->getOptions());

        $task->setOptions(['foo' => 'bar']);
        $this->assertEquals(['foo' => 'bar'], $task->getOptions());

        $task->configure(['baz' => 'qux']);
        $this->assertEquals(['foo' => 'bar', 'baz' => 'qux'], $task->getOptions());
    }

    public function testRespectsDefaults()
    {
        $task     = $this->task('Dependencies');
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
