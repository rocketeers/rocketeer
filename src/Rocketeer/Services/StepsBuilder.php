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
	 * Get and clear the steps
	 *
	 * @return array
	 */
	public function pullSteps()
	{
		$steps = $this->steps;

		$this->steps = [];

		return $steps;
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
