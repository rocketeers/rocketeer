<?php

/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Rocketeer\Traits;

use Rocketeer\Services\StepsBuilder;

/**
 * Gives a class the ability to prepare steps to run and
 * loop over them.
 *
 * @author Maxime Fabre <ehtnam6@gmail.com>
 */
trait StepsRunner
{
    /**
     * @type StepsBuilder
     */
    protected $steps;

    /**
     * @return StepsBuilder
     */
    public function steps()
    {
        if (!$this->steps) {
            $this->steps = new StepsBuilder();
        }

        return $this->steps;
    }

    /**
     * Execute an array of calls until one halts.
     *
     * @return bool
     */
    public function runSteps()
    {
        $steps = $this->steps()->pullSteps();
        foreach ($steps as $step) {
            list($method, $arguments) = $step;
            $arguments                = (array) $arguments;

            $results = call_user_func_array([$this, $method], $arguments);
            $results = $results ?: $this->status();
            if (!$results) {
                return false;
            }
        }

        return true;
    }
}
