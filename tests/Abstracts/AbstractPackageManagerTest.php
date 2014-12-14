<?php
namespace Rocketeer\Abstracts;

use Rocketeer\TestCases\RocketeerTestCase;

class AbstractPackageManagerTest extends RocketeerTestCase
{
	public function testCanGetManifestPath()
	{
		$composer = $this->bash->composer();

		$this->assertEquals($this->app['path.base'].'/'.$composer->getManifest(), $composer->getManifestPath());
	}
}
