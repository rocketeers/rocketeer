<?php
namespace Rocketeer\Strategies;

use Rocketeer\Abstracts\AbstractStrategy;
use Rocketeer\Interfaces\Strategies\DependenciesStrategyInterface;

class ComposerDependenciesStrategy extends AbstractStrategy implements DependenciesStrategyInterface
{
	/**
	 * Install the dependencies
	 *
	 * @return bool
	 */
	public function install()
	{
		// Find Composer
		$composer = $this->composer();
		if (!$this->force and (!$composer->getBinary() or !$this->localStorage->usesComposer())) {
			return true;
		}

		// Get the Composer commands to run
		$tasks = $this->rocketeer->getOption('remote.composer');
		if (!is_callable($tasks)) {
			return true;
		}

		// Cancel if no tasks to execute
		$tasks = (array) $tasks($composer, $this);
		if (empty($tasks)) {
			return true;
		}

		// Run commands
		$this->runForCurrentRelease($tasks);

		return $this->checkStatus('Composer could not install dependencies');
	}

	/**
	 * Update the dependencies
	 *
	 * @return boolean
	 */
	public function update()
	{
		// TODO: Implement update() method.
	}
}
