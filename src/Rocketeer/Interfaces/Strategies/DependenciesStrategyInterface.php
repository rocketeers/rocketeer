<?php
namespace Rocketeer\Interfaces\Strategies;

interface DependenciesStrategyInterface
{
	/**
	 * Install the dependencies
	 *
	 * @return boolean
	 */
	public function install();

	/**
	 * Update the dependencies
	 *
	 * @return boolean
	 */
	public function update();
}
