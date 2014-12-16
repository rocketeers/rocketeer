<?php
namespace Rocketeer\Tasks\Plugins;

use Rocketeer\TestCases\RocketeerTestCase;

class InstallerTest extends RocketeerTestCase
{
	public function testCanInstallPlugin()
	{
		$this->pretend();
		$this->mockCommand(array(
			'package' => 'anahkiasen/rocketeer-slack',
		));

		$this->assertTaskHistory('Plugins\Installer', array(
			'bash --login -c \'{composer} require --working-dir="'.$this->paths->getRocketeerConfigFolder().'"\'',
		));
	}
}
