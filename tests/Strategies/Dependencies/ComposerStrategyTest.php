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
		$composer = $this->builder->buildStrategy('Dependencies', 'Composer');
		$composer->install();

		$this->assertHistory(array(
			array(
				"cd {server}/releases/{release}",
				"{composer} self-update",
				"{composer} install --prefer-source",
			),
		));
	}

	public function testCancelsIfInvalidComposerRoutine()
	{
		$composer = $this->builder->buildStrategy('Dependencies', 'Composer');

		$this->swapConfig(array(
			'rocketeer::strategies.composer.install' => 'lol',
		));

		$composer->install();
		$this->assertHistory([]);

		$this->swapConfig(array(
			'rocketeer::strategies.composer.install' => function () {
				return [];
			},
		));

		$composer->install();
		$this->assertHistory([]);
	}
}
