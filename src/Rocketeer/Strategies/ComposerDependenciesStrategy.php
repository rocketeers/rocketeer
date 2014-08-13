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
		return $this->executeHook('install');
	}

	/**
	 * Update the dependencies
	 *
	 * @return boolean
	 */
	public function update()
	{
		return $this->executeHook('update');
	}

	/**
	 * @param string $hook
	 *
	 * @return bool
	 */
	protected function executeHook($hook)
	{
		$tasks = $this->getHookedTasks('strategies.composer.'.$hook, [$this->composer(), $this]);
		if (!$tasks) {
			return true;
		}

		$this->runForCurrentRelease($tasks);

		return $this->checkStatus('Composer could not install dependencies');
	}
}
