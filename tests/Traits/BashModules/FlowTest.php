<?php
namespace Rocketeer\Traits\BashModules;

use Rocketeer\TestCases\RocketeerTestCase;

class FlowTest extends RocketeerTestCase
{
	public function testCanCopyFilesFromPreviousRelease()
	{
		$this->pretend();
		$this->bash->copyFromPreviousRelease('foobar');

		$this->assertHistory(array(
				'cp -a {server}/releases/10000000000000/foobar {server}/releases/20000000000000/foobar',
		));
	}
}
