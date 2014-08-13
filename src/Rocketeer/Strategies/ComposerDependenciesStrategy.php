<?php
namespace Rocketeer\Strategies;

use Rocketeer\Abstracts\AbstractStrategy;
use Rocketeer\Interfaces\Strategies\DependenciesStrategyInterface;

class ComposerDependenciesStrategy extends AbstractStrategy implements DependenciesStrategyInterface
{
	/**
	 * Whether this particular strategy is runnable or not
	 *
	 * @return boolean
	 */
	public function isExecutable()
	{
		$composer = $this->composer();
		if (!$composer->getBinary() or !$this->localStorage->usesComposer()) {
			return false;
		}

		return true;
	}

	/**
	 * Install the dependencies
	 *
	 * @return bool
	 */
	public function install()
	{
		// Get the tasks to execute
		$tasks = $this->getHookedTasks('strategies.composer.install', [$this->composer(), $this]);
		if (!$tasks) {
			return true;
		}

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
		// Get the tasks to execute
		$tasks = $this->getHookedTasks('strategies.composer.update', [$this->composer(), $this]);
		if (!$tasks) {
			return true;
		}

		$this->runForCurrentRelease($tasks);

		return $this->checkStatus('Composer could not install dependencies');
	}
}
