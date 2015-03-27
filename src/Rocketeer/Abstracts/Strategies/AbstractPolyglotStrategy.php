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
     * The various strategies to call.
     *
     * @type array
     */
    protected $strategies = [];

    /**
     * Results of the last operation that was run.
     *
     * @type array
     */
    protected $results;

    /**
     * Execute a method on all sub-strategies.
     *
     * @param string $method
     *
     * @return boolean[]
     */
    protected function executeStrategiesMethod($method)
    {
        $this->onStrategies(function (AbstractStrategy $strategy) use ($method) {
            return $strategy->$method();
        });

        return $this->passed();
    }

    /**
     * @param Closure $callback
     *
     * @return array
     */
    protected function onStrategies(Closure $callback)
    {
        return $this->explainer->displayBelow(function () use ($callback) {
            $this->results = [];
            foreach ($this->strategies as $strategy) {
                $instance = $this->getStrategy('Dependencies', $strategy);
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
     * Whether the strategy passed or not.
     *
     * @return bool
     */
    public function passed()
    {
        return $this->checkStrategiesResults($this->results);
    }

    /**
     * Assert that the results of a command are all true.
     *
     * @param boolean[] $results
     *
     * @return bool
     */
    protected function checkStrategiesResults($results)
    {
        $results = array_filter($results, function ($value) {
            return $value !== false;
        });

        return count($results) === count($this->strategies);
    }
}
