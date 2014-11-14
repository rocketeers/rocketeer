<?php
namespace Rocketeer\Dummies\Strategies;

use Rocketeer\Abstracts\Strategies\AbstractStrategy;

class NonExecutableStrategy extends AbstractStrategy
{
	public function fire()
	{
		// ...
	}

	public function isExecutable()
	{
		return false;
	}
}
