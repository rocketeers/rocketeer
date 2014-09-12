<?php
namespace Rocketeer\Strategies\Dependencies;

use Rocketeer\TestCases\RocketeerTestCase;

class PolyglotStrategyTest extends RocketeerTestCase
{
	public function testCanInstallAllDependencies()
	{
		$this->usesComposer(true);
		$this->files->put($this->server.'/current/Gemfile', '');

		$polyglot = $this->builder->buildStrategy('Dependencies', 'Polyglot');
		$polyglot->install();

		$this->assertHistory(array(
			array(
				'cd {server}/releases/{release}',
				'{bundle} install',
			),
			array(
				'cd {server}/releases/{release}',
				'{composer} install --no-interaction --no-dev --prefer-dist',
			),
		));
	}
}
