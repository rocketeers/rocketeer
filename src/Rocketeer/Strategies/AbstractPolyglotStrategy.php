<?php

/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace Rocketeer\Strategies;

/**
 * A strategy running other strategies.
 */
abstract class AbstractPolyglotStrategy extends AbstractStrategy
{
    /**
     * The various strategies to call.
     *
     * @var array
     */
    protected $strategies = [];

    /**
     * The type of the sub-strategies.
     *
     * @var string
     */
    protected $type;

    /**
     * Results of the last operation that was run.
     *
     * @var array
     */
    protected $results;

    /**
     * Execute a method on all sub-strategies.
     *
     * @param string $method
     *
     * @return bool
     */
    protected function executeStrategiesMethod($method)
    {
        $this->onStrategies(function (AbstractStrategy $strategy) use ($method) {
            return $strategy->$method();
        });

        return $this->passed();
    }

    /**
     * Execute and check results of a method on all sub-strategies.
     *
     * @param string $method
     *
     * @return bool
     */
    protected function checkStrategiesMethod($method)
    {
        $this->executeStrategiesMethod($method);

        return $this->passed();
    }

    /**
     * @param callable $callback
     *
     * @return array
     */
    protected function onStrategies(callable $callback)
    {
        $this->results = [];
        foreach ($this->strategies as $strategy) {
            $instance = $this->getStrategy($this->type, $strategy, $this->options);
            if ($instance) {
                $this->results[$strategy] = $callback($instance);
                if (!$this->checkResults($this->results[$strategy])) {
                    break;
                }
            } else {
                $this->results[$strategy] = true;
            }
        }

        return $this->results;
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
     * @param bool[] $results
     *
     * @return bool
     */
    protected function checkStrategiesResults($results)
    {
        $results = array_filter($results, function ($value) {
            return $value !== false && (!is_string($value) || mb_strpos($value, 'not found') === false);
        });

        return count($results) === count($this->strategies);
    }
}
