<?php
namespace Rocketeer\Console;

use Rocketeer\TestCases\RocketeerTestCase;

class ConsoleTest extends RocketeerTestCase
{
	public function testCanRunStandaloneConsole()
	{
		$console = exec('php bin/rocketeer --version');

		$this->assertContains('Rocketeer version', $console);
	}
}
