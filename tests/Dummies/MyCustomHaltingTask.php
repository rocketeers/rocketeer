<?php
namespace Rocketeer\Dummies;

use Rocketeer\Abstracts\AbstractTask;

class MyCustomHaltingTask extends AbstractTask
{
	public function execute()
	{
		return $this->halt();
	}
}
