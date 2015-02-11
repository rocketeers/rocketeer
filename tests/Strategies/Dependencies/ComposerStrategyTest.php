<?php
namespace Rocketeer\Strategies\Dependencies;

use Rocketeer\TestCases\RocketeerTestCase;

class ComposerStrategyTest extends RocketeerTestCase
{
    public function testCanConfigureComposerCommands()
    {
        $this->swapConfig(array(
            'rocketeer::scm'                         => array(
                'repository' => 'https://github.com/'.$this->repository,
                'username'   => '',
                'password'   => '',
            ),
            'rocketeer::strategies.composer.install' => function ($composer, $task) {
                return array(
                    $composer->selfUpdate(),
                    $composer->install([], '--prefer-source'),
                );
            },
        ));

        $this->pretendTask();
        $this->tasks->configureStrategy(['Dependencies', 'Composer'], ['flags' => ['install' => ['--prefer-source' => null]]]);
        $this->tasks->listenTo('strategies.dependencies.composer.before', function ($task) {
            $task->composer()->runForCurrentRelease('selfUpdate');
        });

        $composer = $this->builder->buildStrategy('Dependencies', 'Composer');
        $composer->install();

        $this->assertHistory(array(
            array(
                "cd {server}/releases/{release}",
                "{composer} self-update",
            ),
            array(
                "cd {server}/releases/{release}",
                "{composer} install --prefer-source",
            ),
        ));
    }
}
