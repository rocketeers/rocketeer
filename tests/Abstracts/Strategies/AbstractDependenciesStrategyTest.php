<?php
namespace Rocketeer\Abstracts\Strategies;

use Rocketeer\TestCases\RocketeerTestCase;

class AbstractDependenciesStrategyTest extends RocketeerTestCase
{
	public function testCanShareDependenciesFolder()
	{
		$bower = $this->builder->buildStrategy('Dependencies', 'Bower');

		$this->mockFiles(function ($mock) {
			return $mock->shouldReceive('exists')->with($this->paths->getUserHomeFolder().'/.bowerrc')->andReturn(true);
		});

		$this->mock('rocketeer.bash', 'Bash', function ($mock) {
			return $mock->shouldReceive('share')->once()->with('bower_components');
		});

		$this->pretend();
		$bower->configure(['shared_dependencies' => true]);
		$bower->install();
	}

	public function testCanCopyDependencies()
	{
		$bower = $this->builder->buildStrategy('Dependencies', 'Bower');

		$this->mockFiles(function ($mock) {
			return $mock->shouldReceive('exists')->with($this->paths->getUserHomeFolder().'/.bowerrc')->andReturn(true);
		});

		$this->mock('rocketeer.bash', 'Bash', function ($mock) {
			return $mock->shouldReceive('copyFromPreviousRelease')->once()->with('bower_components');
		});

		$this->pretend();
		$bower->configure(['shared_dependencies' => 'copy']);
		$bower->install();
	}
}
