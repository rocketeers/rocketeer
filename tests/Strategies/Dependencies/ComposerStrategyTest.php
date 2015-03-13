<?php

/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Rocketeer\Strategies\Dependencies;

use Rocketeer\TestCases\RocketeerTestCase;

class ComposerStrategyTest extends RocketeerTestCase
{
    public function testCanConfigureComposerCommands()
    {
        $this->swapConfig([
            'scm'                         => [
                'repository' => 'https://github.com/'.$this->repository,
                'username'   => '',
                'password'   => '',
            ],
            'strategies.composer.install' => function ($composer, $task) {
                return [
                    $composer->selfUpdate(),
                    $composer->install([], '--prefer-source'),
                ];
            },
        ]);

        $this->pretendTask();
        $this->tasks->configureStrategy(['Dependencies', 'Composer'], ['flags' => ['install' => ['--prefer-source' => null]]]);
        $this->tasks->listenTo('strategies.dependencies.composer.before', function ($task) {
            $task->composer()->runForCurrentRelease('selfUpdate');
        });

        $composer = $this->builder->buildStrategy('Dependencies', 'Composer');
        $composer->install();

        $this->assertHistory([
            [
                "cd {server}/releases/{release}",
                "{composer} self-update",
            ],
            [
                "cd {server}/releases/{release}",
                "{composer} install --prefer-source",
            ],
        ]);
    }
}
