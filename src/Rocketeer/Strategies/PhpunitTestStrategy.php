<?php
namespace Rocketeer\Strategies;

use Rocketeer\Abstracts\AbstractStrategy;
use Rocketeer\Interfaces\Strategies\TestStrategyInterface;

class PhpunitTestStrategy extends AbstractStrategy implements TestStrategyInterface
{
	/**
	 * Whether this particular strategy is runnable or not
	 *
	 * @return boolean
	 */
	public function isExecutable()
	{
		return (bool) $this->phpunit()->getBinary();
	}

	/**
	 * Run the task
	 *
	 * @return boolean
	 */
	public function test()
	{
		// Run PHPUnit
		$arguments = ['--stop-on-failure' => null];
		$output    = $this->runForCurrentRelease(array(
			$this->phpunit()->getCommand(null, [], $arguments),
		));

		$status = $this->checkStatus('Tests failed', $output, 'Tests passed successfully');
		if (!$status) {
			$this->command->error('Tests failed');
		}

		return $status;
	}
}
