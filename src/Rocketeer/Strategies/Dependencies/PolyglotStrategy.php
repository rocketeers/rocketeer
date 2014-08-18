<?php
/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Rocketeer\Strategies\Dependencies;

use Closure;
use Rocketeer\Abstracts\Strategies\AbstractDependenciesStrategy;
use Rocketeer\Abstracts\Strategies\AbstractStrategy;
use Rocketeer\Interfaces\Strategies\DependenciesStrategyInterface;

class PolyglotStrategy extends AbstractStrategy implements DependenciesStrategyInterface
{
	/**
	 * @type string
	 */
	protected $description = 'Runs all of the above package managers if necessary';

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
		return $this->onManagers(function (AbstractDependenciesStrategy $manager) {
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
		return $this->onManagers(function (AbstractDependenciesStrategy $manager) {
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
