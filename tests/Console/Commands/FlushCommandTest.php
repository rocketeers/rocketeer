<?php
namespace Rocketeer\Console\Commands;

use Rocketeer\TestCases\RocketeerTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class FlushCommandTest extends RocketeerTestCase
{
	public function testCanFlushLocalStorage()
	{
		$this->localStorage->set('foo', 'bar');

		$this->assertEquals('bar', $this->localStorage->get('foo'));
		$tester = $this->executeCommand('flush');

		$this->assertContains('has been properly', $tester->getDisplay());
		$this->assertNull($this->localStorage->get('foo'));
	}
}
