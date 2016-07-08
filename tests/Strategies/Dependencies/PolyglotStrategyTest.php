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

namespace Rocketeer\Strategies\Dependencies;

use Mockery;
use Mockery\MockInterface;
use Rocketeer\Bash;
use Rocketeer\TestCases\RocketeerTestCase;

class PolyglotStrategyTest extends RocketeerTestCase
{
    public function testCanInstallAllDependencies()
    {
        $this->pretend();
        $this->usesComposer();
        $this->usesBundler();

        $polyglot = $this->builder->buildStrategy('Dependencies', 'Polyglot');
        $polyglot->install();

        $this->assertHistory([
            [
                'cd {server}/releases/{release}',
                '{bundle} install',
            ],
            [
                'cd {server}/releases/{release}',
                '{composer} install --no-interaction --no-dev --prefer-dist',
            ],
        ]);
    }

    public function testProperlyChecksResults()
    {
        $this->pretend();

        $this->mock(Bash::class, 'Bash', function (MockInterface $mock) {
            return $mock
                ->shouldReceive('fileExists')->andReturn(true)
                ->shouldReceive('which')->with('composer', Mockery::any(), false)->andReturn('composer')
                ->shouldReceive('which')->with('bundle', Mockery::any(), false)->andReturn('bundle')
                ->shouldReceive('runForCurrentRelease')->with('composer install')->andReturn('YUP')
                ->shouldReceive('runForCurrentRelease')->with('bundle install')->andReturn('bash: bundler: command not found');
        });

        $this->usesComposer();
        $this->usesBundler();

        $polyglot = $this->builder->buildStrategy('Dependencies', 'Polyglot');
        $results = $polyglot->install();

        $this->assertFalse($results);
    }
}
