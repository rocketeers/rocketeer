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

class ComposerStrategyTest extends RocketeerTestCase
{
    public function testCanConfigureComposerCommands()
    {
        $this->swapConfig([
            'rocketeer::scm' => [
                'repository' => 'https://github.com/'.$this->repository,
                'username' => '',
                'password' => '',
            ],
            'rocketeer::strategies.composer.install' => function ($composer, $task) {
                return [
                    $composer->selfUpdate(),
                    $composer->install([], '--prefer-source'),
                ];
            },
        ]);

        $this->pretendTask();
        $composer = $this->builder->buildStrategy('Dependencies', 'Composer');
        $composer->install();

        $this->assertHistory([
            [
                'cd {server}/releases/{release}',
                '{composer} self-update',
                '{composer} install --prefer-source',
            ],
        ]);
    }

    public function testCancelsIfInvalidComposerRoutine()
    {
        $composer = $this->builder->buildStrategy('Dependencies', 'Composer');

        $this->swapConfig([
            'rocketeer::strategies.composer.install' => 'lol',
        ]);

        $composer->install();
        $this->assertHistory([]);

        $this->swapConfig([
            'rocketeer::strategies.composer.install' => function () {
                return [];
            },
        ]);

        $composer->install();
        $this->assertHistory([]);
    }
}
