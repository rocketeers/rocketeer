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

	public function testRespectsDefaults()
	{
		$task = $this->builder->buildTask('Dependencies');
		$strategy = $this->builder->buildStrategy('Dependencies', 'Composer');

		$this->assertFalse($task->getOption('shared_dependencies', true));
		$this->assertFalse($strategy->getOption('shared_dependencies', true));
	}
}
