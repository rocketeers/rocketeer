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

use Prophecy\Argument;
use Rocketeer\Services\Connections\Shell\Bash;
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

        /** @var Bash $prophecy */
        $prophecy = $this->bindProphecy(Bash::class);
        $prophecy->fileExists()->willReturn(true);
        $prophecy->which(Argument::cetera())->willReturnArgument(0);
        $prophecy->runForApplication(Argument::cetera())->willReturn(true);
        $prophecy->listContents(Argument::cetera())->willReturn();
        $prophecy->fileExists(Argument::cetera())->willReturn(true);
        $prophecy->runForApplication('bundle install')->willReturn('bash: bundler: command not found');

        $this->usesComposer();
        $this->usesBundler();

        /** @var PolyglotStrategy $polyglot */
        $polyglot = $this->builder->buildStrategy('Dependencies', 'Polyglot');
        $results = $polyglot->install();

        $this->assertFalse($results);
    }
}
