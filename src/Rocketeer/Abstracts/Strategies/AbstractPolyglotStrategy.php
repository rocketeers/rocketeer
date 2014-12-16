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

abstract class AbstractPolyglotStrategy extends AbstractStrategy
{
	/**
	 * The various strategies to call
	 *
	 * @type array
	 */
	protected $strategies = [];

	/**
	 * The type of the sub-strategies
	 *
	 * @type string
	 */
	protected $type;

	/**
	 * Results of the last operation that was run
	 *
	 * @type array
	 */
	protected $results;

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
	 * Gather the missing X from a method
	 *
	 * @param string $method
	 *
	 * @return string[]
	 */
	protected function gatherMissingFromMethod($method)
	{
		$missing = [];
		$gathered = $this->executeStrategiesMethod($method);
		foreach ($gathered as $value) {
			$missing = array_merge($missing, $value);
		}

		return $missing;
	}

	/**
	 * @param callable $callback
	 *
	 * @return array
	 */
	protected function onStrategies(callable $callback)
	{
		return $this->explainer->displayBelow(function () use ($callback) {
			$this->results = [];
			foreach ($this->strategies as $strategy) {
				$instance = $this->getStrategy($this->type, $strategy, $this->options);
				if ($instance) {
					$this->results[$strategy] = $callback($instance);
					if (!$this->results[$strategy]) {
						break;
					}
				} else {
					$this->results[$strategy] = true;
				}
			}

			return $this->results;
		});
	}

	//////////////////////////////////////////////////////////////////////
	////////////////////////////// RESULTS ///////////////////////////////
	//////////////////////////////////////////////////////////////////////

	/**
	 * Whether the strategy passed or not
	 *
	 * @return boolean
	 */
	public function passed()
	{
		return $this->checkStrategiesResults($this->results);
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
		$results = array_filter($results, function ($value) {
			return $value !== false;
		});

		return count($results) === count($this->strategies);
	}
}
