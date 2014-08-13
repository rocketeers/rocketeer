<?php
namespace Rocketeer\Strategies\Dependencies;

use Rocketeer\Abstracts\Strategies\AbstractDependenciesStrategy;
use Rocketeer\Abstracts\Strategies\AbstractStrategy;
use Rocketeer\Interfaces\Strategies\DependenciesStrategyInterface;

class BundlerStrategy extends AbstractDependenciesStrategy implements DependenciesStrategyInterface
{
	/**
	 * The name of the manifest file to look for
	 *
	 * @type string
	 */
	protected $manifest = 'Gemfile';

	/**
	 * The name of the binary
	 *
	 * @type string
	 */
	protected $binary = 'bundle';

	/**
	 * Install the dependencies
	 *
	 * @return bool
	 */
	public function install()
	{
		return $this->getManager()->runForCurrentRelease('install');
	}

	/**
	 * Update the dependencies
	 *
	 * @return boolean
	 */
	public function update()
	{
		return $this->getManager()->runForCurrentRelease('update');
	}
}
