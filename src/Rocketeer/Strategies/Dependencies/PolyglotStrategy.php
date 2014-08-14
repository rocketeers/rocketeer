<?php
namespace Rocketeer\Strategies\Dependencies;

use Closure;
use Rocketeer\Abstracts\Strategies\AbstractStrategy;
use Rocketeer\Interfaces\Strategies\DependenciesStrategyInterface;

class PolyglotStrategy extends AbstractStrategy implements DependenciesStrategyInterface
{
	/**
	 * The various dependencies managers
	 *
	 * @type array
	 */
	protected $managers = ['Bundler', 'Composer', 'Npm', 'Bower'];

	/**
	 * Install the dependencies
	 *
	 * @return boolean
	 */
	public function install()
	{
		return $this->onManagers(function ($manager) {
			return $manager->install();
		});
	}

	/**
	 * Update the dependencies
	 *
	 * @return boolean
	 */
	public function update()
	{
		return $this->onManagers(function ($manager) {
			return $manager->update();
		});
	}

	/**
	 * @param Closure $callback
	 *
	 * @return array
	 */
	protected function onManagers(Closure $callback)
	{
		$results = [];
		foreach ($this->managers as $manager) {
			$strategy = $this->getStrategy('Dependencies', $manager);
			if ($strategy) {
				$results[$manager] = $callback($strategy);
			}
		}

		return $results;
	}
}
