<?php
class ConsoleTest extends RocketeerTests
{
	public function testCanRunStandaloneConsole()
	{
		$console = exec('php rocketeer --version');

		$this->assertContains('Rocketeer version 0', $console);
	}
}
