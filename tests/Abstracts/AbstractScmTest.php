<?php
namespace Rocketeer\Abstracts;

use Rocketeer\Scm\Git;
use Rocketeer\TestCases\RocketeerTestCase;

class AbstractScmTest extends RocketeerTestCase
{
	public function testCanGetSprintfCommand()
	{
		$scm     = new Git($this->app);
		$command = $scm->getCommand('foo %s', 'bar');

		$this->assertEquals('git foo bar', $command);
	}

	public function testCanExecuteMethod()
	{
		$this->mock('rocketeer.bash', 'Bash', function ($mock) {
			return $mock->shouldReceive('run')->once()->withAnyArgs()->andReturnUsing(function ($arguments) {
				return $arguments;
			});
		});

		$scm      = new Git($this->app);
		$command  = $scm->execute('checkout', $this->server);
		$expected = $this->replaceHistoryPlaceholders(['git clone --depth 1 -b master "{repository}" {server}']);

		$this->assertEquals($expected[0], $command);
	}
}
