<?php
namespace Rocketeer\Traits;

use Rocketeer\Scm\Git;
use Rocketeer\TestCases\RocketeerTestCase;

class ScmTest extends RocketeerTestCase
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

		$scm     = new Git($this->app);
		$command = $scm->execute('checkout', $this->server);

		$this->assertEquals('git clone --depth 1 -b master "" '.$this->server, $command);
	}
}
