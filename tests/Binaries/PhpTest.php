<?php
namespace Rocketeer\Binaries;

use Rocketeer\TestCases\RocketeerTestCase;

class PhpTest extends RocketeerTestCase
{
	public function testCanCheckIfUsesHhvm()
	{
		$php     = new Php($this->app);
		$hhvm    = $php->isHhvm();
		$defined = defined('HHVM_VERSION');

		$this->assertEquals($defined, $hhvm);
	}
}
