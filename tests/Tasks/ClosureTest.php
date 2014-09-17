<?php
namespace Rocketeer\Tasks;

use Rocketeer\TestCases\RocketeerTestCase;

class ClosureTest extends RocketeerTestCase
{
	public function testCanGetDescriptionOfClosureTask()
	{
		$closure = $this->builder->buildTask(['ls', 'ls'], 'FilesLister');

		$this->assertEquals('FilesLister', $closure->getName());
		$this->assertEquals('files-lister', $closure->getSlug());
		$this->assertEquals('ls/ls', $closure->getDescription());
	}
}
