<?php
/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Rocketeer\Abstracts\Strategies;

use Closure;

abstract class AbstractPolyglotStrategy extends AbstractStrategy
{
	/**
	 * The various strategies to call
	 *
	 * @type array
	 */
	protected $strategies = [];

	/**
	 * Execute a method on all sub-strategies
	 *
	 * @param string $method
	 *
	 * @return boolean[]
	 */
	protected function executeStrategiesMethod($method)
	{
		return $this->onStrategies(function (AbstractStrategy $strategy) use ($method) {
			return $strategy->$method();
		});
	}

	/**
	 * Assert that the results of a command are all true
	 *
	 * @param boolean[] $results
	 *
	 * @return boolean
	 */
	protected function checkStrategiesResults($results)
	{
		$results = array_filter($results);

		return count($results) == count($this->strategies);
	}

	/**
	 * @param Closure $callback
	 *
	 * @return array
	 */
	protected function onStrategies(Closure $callback)
	{
		return $this->explainer->displayBelow(function () use ($callback) {
			$results = [];
			foreach ($this->strategies as $strategy) {
				$instance = $this->getStrategy('Dependencies', $strategy);
				if ($instance) {
					$results[$strategy] = $callback($instance);
				}
			}

			return $results;
		});
	}
}
