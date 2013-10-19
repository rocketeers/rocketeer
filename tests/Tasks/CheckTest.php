<?php

class CheckTest extends RocketeerTests
{
	public function testCanPretendToCheck()
	{
		$task = $this->pretendTask('Check');
		$task->execute();
	}
}
