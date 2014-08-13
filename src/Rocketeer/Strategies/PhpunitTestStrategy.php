<?php
namespace Rocketeer\Strategies;

use Rocketeer\Abstracts\AbstractStrategy;
use Rocketeer\Interfaces\Strategies\TestStrategyInterface;

class PhpunitTestStrategy extends AbstractStrategy implements TestStrategyInterface
{
	/**
	 * Run the task
	 *
	 * @return boolean
	 */
	public function test()
	{
		// Look for PHPUnit
		$phpunit = $this->phpunit();
		if (!$phpunit->getBinary()) {
			return true;
		}

		// Run PHPUnit
		$arguments = ['--stop-on-failure' => null];
		$output    = $this->runForCurrentRelease(array(
			$phpunit->getCommand(null, [], $arguments),
		));

		$status = $this->checkStatus('Tests failed', $output, 'Tests passed successfully');
		if (!$status) {
			$this->command->error('Tests failed');
		}

		return $status;
	}

	/**
	 * Run the task
	 *
	 * @return string
	 */
	public function execute()
	{
		// ...
	}
}
