<?php
namespace Rocketeer\Strategies\Dependencies;

use Rocketeer\Abstracts\Strategies\AbstractDependenciesStrategy;
use Rocketeer\Abstracts\Strategies\AbstractStrategy;
use Rocketeer\Interfaces\Strategies\DependenciesStrategyInterface;

class ComposerStrategy extends AbstractDependenciesStrategy implements DependenciesStrategyInterface
{
	/**
	 * The name of the manifest file to look for
	 *
	 * @type string
	 */
	protected $manifest = 'composer.json';

	/**
	 * The name of the binary
	 *
	 * @type string
	 */
	protected $binary = 'composer';

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
		$tasks = $this->getHookedTasks('strategies.composer.'.$hook, [$this->getManager(), $this]);
		if (!$tasks) {
			return true;
		}

		$this->runForCurrentRelease($tasks);

		return $this->checkStatus('Composer could not install dependencies');
	}
}
