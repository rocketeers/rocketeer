<?php
namespace Rocketeer\Binaries;

use Rocketeer\Binaries\AnonymousBinary;
use Rocketeer\TestCases\RocketeerTestCase;

class AnonymousBinaryTest extends RocketeerTestCase
{
	public function testCanCreateAnonymousBinaries()
	{
		$anonymous = new AnonymousBinary($this->app);
		$anonymous->setBinary('foobar');

		$this->assertEquals('foobar foo bar --lol', $anonymous->foo('bar', '--lol'));
	}
}
