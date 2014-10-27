<?php
namespace Rocketeer\Console\Commands;

use Rocketeer\Rocketeer;
use Rocketeer\TestCases\RocketeerTestCase;

class RocketeerCommandTest extends RocketeerTestCase
{
	public function testCanDisplayVersion()
	{
		$tester = $this->executeCommand(null, array(
			'--version' => null,
		));

		$output = $tester->getDisplay(true);
		$output = trim($output);

		$this->assertEquals('Rocketeer version '.Rocketeer::VERSION, $output);
	}
}
