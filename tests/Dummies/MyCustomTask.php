<?php
namespace Rocketeer\Dummies;

use Rocketeer\Abstracts\Task;

class MyCustomTask extends Task
{
	public function execute()
	{
		return 'foobar';
	}
}
