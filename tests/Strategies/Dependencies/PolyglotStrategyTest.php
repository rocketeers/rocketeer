<?php
namespace Rocketeer\Strategies\Dependencies;

use Mockery;
use Mockery\MockInterface;
use Rocketeer\TestCases\RocketeerTestCase;

class PolyglotStrategyTest extends RocketeerTestCase
{
    public function testCanInstallAllDependencies()
    {
        $this->pretend();
        $this->usesComposer(true);
        $this->files->put($this->server.'/current/Gemfile', '');

        $polyglot = $this->builder->buildStrategy('Dependencies', 'Polyglot');
        $polyglot->install();

        $this->assertHistory(array(
            array(
                'cd {server}/releases/{release}',
                '{bundle} install',
            ),
            array(
                'cd {server}/releases/{release}',
                '{composer} install --no-interaction --no-dev --prefer-dist',
            ),
        ));
    }

    public function testProperlyChecksResults()
    {
        $this->pretend();

        $this->mock('rocketeer.bash', 'Bash', function (MockInterface $mock) {
           return $mock
               ->shouldReceive('fileExists')->andReturn(true)
               ->shouldReceive('which')->with('composer', Mockery::any(), false)->andReturn('composer')
               ->shouldReceive('which')->with('bundle', Mockery::any(), false)->andReturn('bundle')
               ->shouldReceive('runForCurrentRelease')->with('composer install')->andReturn('YUP')
               ->shouldReceive('runForCurrentRelease')->with('bundle install')->andReturn('bash: bundler: command not found');
        });

        $this->usesComposer(true);
        $this->files->put($this->server.'/current/Gemfile', '');

        $polyglot = $this->builder->buildStrategy('Dependencies', 'Polyglot');
        $results  = $polyglot->install();

        $this->assertFalse($results);
    }
}
