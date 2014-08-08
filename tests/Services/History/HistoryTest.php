<?php
namespace Services\History;

use Rocketeer\TestCases\RocketeerTestCase;

class HistoryTest extends RocketeerTestCase
{
	public function testCanGetFlattenedHistory()
	{
		$this->bash->toHistory('foo');
		sleep(1);
		$this->bash->toHistory(['bar', 'baz']);

		$history = $this->history->getFlattenedHistory();
		$this->assertEquals(['foo', ['bar', 'baz']], $history);
	}

	public function testCanGetFlattenedOutput()
	{
		$this->bash->toOutput('foo');
		sleep(1);
		$this->bash->toOutput(['bar', 'baz']);

		$history = $this->history->getFlattenedOutput();
		$this->assertEquals(['foo', ['bar', 'baz']], $history);
	}
}
