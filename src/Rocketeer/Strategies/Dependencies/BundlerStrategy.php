<?php
namespace Rocketeer\Strategies\Dependencies;

use Rocketeer\Abstracts\Strategies\AbstractDependenciesStrategy;
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
}
