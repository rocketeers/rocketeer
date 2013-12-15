<?php
namespace Rocketeer\Tests\Dummies;

use Rocketeer\Traits\Task;

class MyCustomTask extends Task
{
	public function execute()
	{
		return 'foobar';
	}
}
