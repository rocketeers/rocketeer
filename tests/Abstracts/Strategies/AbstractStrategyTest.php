<?php
namespace Rocketeer\Abstracts\Strategies;

use Rocketeer\TestCases\RocketeerTestCase;

class AbstractStrategyTest extends RocketeerTestCase
{
	public function testCanCheckForManifestWithoutServer()
	{
		$this->app['path.base'] = realpath(__DIR__.'/../../..');

		$this->usesComposer(false);
		$strategy = $this->builder->buildStrategy('Dependencies', 'Composer');
		$this->assertTrue($strategy->isExecutable());
	}
}
