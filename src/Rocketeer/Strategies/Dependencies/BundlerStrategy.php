<?php
namespace Rocketeer\Strategies\Dependencies;

use Rocketeer\Abstracts\AbstractStrategy;
use Rocketeer\Interfaces\Strategies\DependenciesStrategyInterface;

class BundlerStrategy extends AbstractStrategy implements DependenciesStrategyInterface
{
	/**
	 * Whether this particular strategy is runnable or not
	 *
	 * @return boolean
	 */
	public function isExecutable()
	{
		$bundler = $this->binary('bundle');
		if (!$bundler->getBinary() or !$this->bash->fileExists($this->rocketeer->getFolder('Gemfile'))) {
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
		return $this->binary('bundle')->install();
	}

	/**
	 * Update the dependencies
	 *
	 * @return boolean
	 */
	public function update()
	{
		return $this->binary('bundle')->update();
	}
}
