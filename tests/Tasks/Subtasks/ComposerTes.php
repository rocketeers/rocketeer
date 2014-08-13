<?php
namespace Tasks\Subtasks;

use Rocketeer\TestCases\RocketeerTestCase;

class ComposerTest extends RocketeerTestCase
{
	public function testCanConfigureComposerCommands()
	{
		$this->swapConfig(array(
			'rocketeer::scm'             => array(
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

		$matcher = array(
			array(
				"cd {server}/releases/{release}",
				"{composer} self-update",
				"{composer} install --prefer-source",
			),
		);

		$composer        = $this->pretendTask('Dependencies')->getStrategy('Dependencies', true);
		$composer->install();

		$this->assertTaskHistory($this->history->getFlattenedHistory(), $matcher, array(
			'tests'   => false,
			'seed'    => false,
			'migrate' => false
		));
	}

	public function testCancelsIfInvalidComposerRoutine()
	{
		$composer        = $this->pretendTask('Dependencies');
		$composer->force = true;

		$this->swapConfig(array(
			'rocketeer::strategies.composer.install' => 'lol',
		));

		$composer->fire();
		$this->assertEmpty($this->history->getFlattenedHistory());

		$this->swapConfig(array(
			'rocketeer::strategies.composer.install' => function () {
				return [];
			},
		));

		$composer->fire();
		$this->assertEmpty($this->history->getFlattenedHistory());
	}
}
