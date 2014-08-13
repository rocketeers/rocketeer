<?php
namespace Rocketeer\Interfaces\Strategies;

interface TestStrategyInterface
{
	/**
	 * Run the tests
	 *
	 * @return boolean
	 */
	public function test();
}
