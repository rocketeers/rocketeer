<?php
namespace Rocketeer\Tests\Console;

use Rocketeer\Tests\RocketeerTests;

class ConsoleTest extends RocketeerTests
{
	public function testCanRunStandaloneConsole()
	{
		$console = exec('php bin/rocketeer --version');

		$this->assertContains('Rocketeer version', $console);
	}
}
