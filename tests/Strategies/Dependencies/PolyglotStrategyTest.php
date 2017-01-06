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

use Rocketeer\TestCases\RocketeerTestCase;

class PolyglotStrategyTest extends RocketeerTestCase
{
    public function testCanInstallAllDependencies()
    {
        $this->usesComposer(true);
        $this->files->put($this->server.'/current/Gemfile', '');

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
}
