<?php
namespace Rocketeer\Services\History;

use Rocketeer\TestCases\RocketeerTestCase;

class HistoryTest extends RocketeerTestCase
{
	/**
	 * @type integer
	 */
	protected $sleep = 5;

	public function testCanGetFlattenedHistory()
	{
		$this->bash->toHistory('foo');
		usleep($this->sleep);
		$this->bash->toHistory(['bar', 'baz']);

		$history = $this->history->getFlattenedHistory();
		$this->assertEquals(['foo', ['bar', 'baz']], $history);
	}

	public function testCanGetFlattenedOutput()
	{
		$this->bash->toOutput('foo');
		usleep($this->sleep);
		$this->bash->toOutput(['bar', 'baz']);

		$history = $this->history->getFlattenedOutput();
		$this->assertEquals(['foo', ['bar', 'baz']], $history);
	}
}
