<?php
namespace Rocketeer\Services\Tasks;

use Rocketeer\TestCases\RocketeerTestCase;

class JobTest extends RocketeerTestCase
{
	public function testCanCreateBasicJob()
	{
		$this->swapConfig(['rocketeer::default' => ['production', 'staging']]);

		$pipeline = $this->queue->buildPipeline(['ls']);

		$this->assertInstanceOf('Illuminate\Support\Collection', $pipeline);
		$this->assertCount(2, $pipeline);
		$this->assertInstanceOf('Rocketeer\Services\Tasks\Job', $pipeline[0]);
		$this->assertInstanceOf('Rocketeer\Services\Tasks\Job', $pipeline[1]);

		$this->assertEquals(['ls'], $pipeline[0]->queue);
		$this->assertEquals(['ls'], $pipeline[1]->queue);

		$this->assertEquals('production', $pipeline[0]->connection);
		$this->assertEquals('staging', $pipeline[1]->connection);
	}
}
