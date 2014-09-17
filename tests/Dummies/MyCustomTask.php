<?php
namespace Rocketeer\Dummies;

use Rocketeer\Abstracts\AbstractTask;

class MyCustomTask extends AbstractTask
{
	public function execute()
	{
		return 'foobar';
	}
}
