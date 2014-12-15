<?php
namespace Rocketeer\Traits;

use Rocketeer\TestCases\RocketeerTestCase;

class ConfigurableTest extends RocketeerTestCase
{
	public function testCanConfigureTask()
	{
		$task = $this->builder->buildTask('Deploy');
		$this->assertEmpty($task->getOptions());

		$task->setOptions(['foo' => 'bar']);
		$this->assertEquals(['foo' => 'bar'], $task->getOptions());

		$task->configure(['baz' => 'qux']);
		$this->assertEquals(['foo' => 'bar', 'baz' => 'qux'], $task->getOptions());
	}
}
