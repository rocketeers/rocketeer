<?php
namespace Rocketeer\Binaries;

use Rocketeer\Binaries\PackageManagers\Composer;
use Rocketeer\TestCases\RocketeerTestCase;

class ComposerTest extends RocketeerTestCase
{
	public function testCanWrapWithPhpIfArchive()
	{
		$composer = new Composer($this->app);
		$composer->setBinary('composer.phar');

		$this->assertEquals($this->binaries['php'].' composer.phar install', $composer->install());
	}
}
