<?php
namespace Rocketeer\Console\Commands;

use Rocketeer\TestCases\RocketeerTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class FlushCommandTest extends RocketeerTestCase
{
	public function testCanFlushLocalStorage()
	{
		$flush = $this->app['rocketeer.commands.flush'];
		$this->localStorage->set('foo', 'bar');

		$this->assertEquals('bar', $this->localStorage->get('foo'));
		$tester = new CommandTester($flush);
		$tester->execute(['command' => $flush->getName()]);

		$this->assertContains('has been properly', $tester->getDisplay());
		$this->assertNull($this->localStorage->get('foo'));
	}
}
