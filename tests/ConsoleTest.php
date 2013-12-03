<?php
class ConsoleTest extends RocketeerTests
{
	public function testCanRunStandaloneConsole()
	{
		$console = exec('php bin/rocketeer --version');

		$this->assertContains('Rocketeer version 1', $console);
	}
}
