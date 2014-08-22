<?php
namespace Rocketeer\Strategies\Check;

use Rocketeer\Abstracts\Strategies\AbstractCheckStrategy;
use Rocketeer\Interfaces\Strategies\CheckStrategyInterface;

/**
 * Strategy for Node projects
 *
 * @author Maxime Fabre <ehtnam6@gmail.com>
 */
class NodeStrategy extends AbstractCheckStrategy implements CheckStrategyInterface
{
	/**
	 * @type string
	 */
	protected $description = 'Checks if the server is ready to receive a Node application';

	/**
	 * The language of the strategy
	 *
	 * @type string
	 */
	protected $language = 'Node';

	/**
	 * Get the version constraint which should be checked against
	 *
	 * @param string $manifest
	 *
	 * @return string
	 */
	protected function getLanguageConstraint($manifest)
	{
		return $this->getLanguageConstraintFromJson($manifest, 'engines.node');
	}

	/**
	 * Get the current version in use
	 *
	 * @return string
	 */
	protected function getCurrentVersion()
	{
		$version = $this->binary('node')->run('--version');
		$version = str_replace('v', null, $version);

		return $version;
	}

	/**
	 * Check for the required extensions
	 *
	 * @return array
	 */
	public function extensions()
	{
		return [];
	}

	/**
	 * Check for the required drivers
	 *
	 * @return array
	 */
	public function drivers()
	{
		return [];
	}
}
