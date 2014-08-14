<?php
namespace Rocketeer\Strategies\Dependencies;

use Rocketeer\Abstracts\Strategies\AbstractDependenciesStrategy;
use Rocketeer\Interfaces\Strategies\DependenciesStrategyInterface;

class NpmStrategy extends AbstractDependenciesStrategy implements DependenciesStrategyInterface
{
	/**
	 * The name of the manifest file to look for
	 *
	 * @type string
	 */
	protected $manifest = 'package.json';

	/**
	 * The name of the binary
	 *
	 * @type string
	 */
	protected $binary = 'npm';
}
