<?php
namespace Rocketeer\Services;

class StepsBuilder
{
	/**
	 * The extisting steps
	 *
	 * @type array
	 */
	protected $steps = [];

	/**
	 * Add a step
	 *
	 * @param string $name
	 * @param array  $arguments
	 */
	public function __call($name, $arguments)
	{
		$this->steps[] = [$name, $arguments];
	}

	/**
	 * Get the steps to execute
	 *
	 * @return array
	 */
	public function getSteps()
	{
		return $this->steps;
	}
}
