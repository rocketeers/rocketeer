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
			'rocketeer::remote.composer' => function ($composer, $task) {
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

		$composer        = $this->pretendTask('Composer');
		$composer->force = true;
		$composer->fire();

		$this->assertTaskHistory($this->history->getFlattenedHistory(), $matcher, array(
			'tests'   => false,
			'seed'    => false,
			'migrate' => false
		));
	}

	public function testCancelsIfInvalidComposerRoutine()
	{
		$composer        = $this->pretendTask('Composer');
		$composer->force = true;

		$this->swapConfig(array(
			'rocketeer::remote.composer' => 'lol',
		));

		$composer->fire();
		$this->assertEmpty($this->history->getFlattenedHistory());

		$this->swapConfig(array(
			'rocketeer::remote.composer' => function () {
				return [];
			},
		));

		$composer->fire();
		$this->assertEmpty($this->history->getFlattenedHistory());
	}
}
