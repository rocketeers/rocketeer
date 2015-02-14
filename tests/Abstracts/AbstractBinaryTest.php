<?php
namespace Rocketeer\Abstracts;

use Mockery\MockInterface;
use Rocketeer\Binaries\Scm\Git;
use Rocketeer\TestCases\RocketeerTestCase;

class AbstractBinaryTest extends RocketeerTestCase
{
    public function testCanExecuteMethod()
    {
        $this->mock('rocketeer.bash', 'Bash', function (MockInterface $mock) {
            return $mock->shouldReceive('run')->once()->withAnyArgs()->andReturnUsing(function ($arguments) {
                return $arguments;
            });
        });

        $scm      = new Git($this->app);
        $command  = $scm->run('checkout', $this->server);
        $expected = $this->replaceHistoryPlaceholders(['git clone "{repository}" "{server}" --branch="master" --depth="1"']);

        $this->assertEquals($expected[0], $command);
    }

    public function testCanProperlyBuildMultivalueOptions()
    {
        $binary  = new Git($this->app);
        $command = $binary->getCommand('foobar', [], ['--foo' => ['bar', 'baz']]);

        $this->assertEquals('git foobar --foo="bar" --foo="baz"', $command);
    }

    public function testCanBuildOptinsIfNoKeysSpecified()
    {
        $binary  = new Git($this->app);
        $command = $binary->getCommand('foobar', [], ['--foo', '--bar']);

        $this->assertEquals('git foobar --foo --bar', $command);
    }
}
