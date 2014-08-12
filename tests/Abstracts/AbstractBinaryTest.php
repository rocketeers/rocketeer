<?php
namespace Rocketeer\Abstracts;

use Rocketeer\Scm\Git;
use Rocketeer\TestCases\RocketeerTestCase;

class AbstractBinaryTest extends RocketeerTestCase
{
	public function testCanExecuteMethod()
	{
		$this->mock('rocketeer.bash', 'Bash', function ($mock) {
			return $mock->shouldReceive('run')->once()->withAnyArgs()->andReturnUsing(function ($arguments) {
				return $arguments;
			});
		});

		$scm      = new Git($this->app);
		$command  = $scm->run('checkout', $this->server);
		$expected = $this->replaceHistoryPlaceholders(['git clone "{repository}" "{server}" --branch="master" --depth="1"']);

		$this->assertEquals($expected[0], $command);
	}
}
