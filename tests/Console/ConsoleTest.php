<?php
namespace Rocketeer\Tests\Console;

use Rocketeer\Tests\TestCases\RocketeerTestCase;

class ConsoleTest extends RocketeerTestCase
{
	public function testCanRunStandaloneConsole()
	{
		$console = exec('php bin/rocketeer --version');

		$this->assertContains('Rocketeer version', $console);
	}
}
