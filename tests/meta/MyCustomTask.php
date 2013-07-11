<?php
namespace Tasks;

use Rocketeer\Traits\Task;

class MyCustomTask extends Task
{
	public function execute()
	{
		return 'foobar';
	}
}
