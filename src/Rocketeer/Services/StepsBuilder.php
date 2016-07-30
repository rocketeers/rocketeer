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

namespace Rocketeer\Services;

/**
 * Saves in an array methods the call signatures
 * of the methods called on it.
 */
class StepsBuilder
{
    /**
     * The extisting steps.
     *
     * @var array
     */
    protected $steps = [];

    /**
     * Add a step.
     *
     * @param string $name
     * @param array  $arguments
     */
    public function __call($name, $arguments)
    {
        $this->addStep($name, $arguments);
    }

    /**
     * Add a callable to the steps.
     *
     * @param string|callable $callable
     * @param array           $arguments
     */
    public function addStep($callable, $arguments = [])
    {
        $this->steps[] = [$callable, $arguments];
    }

    /**
     * Add a step and fire an event before/after.
     *
     * @param string          $event
     * @param string|callable $callable
     * @param array           $arguments
     */
    public function addStepWithEvents($event, $callable, $arguments = [])
    {
        $this->addStep('fireEvent', [$event.'.before']);
        $this->addStep($callable, $arguments);
        $this->addStep('fireEvent', [$event.'.after']);
    }

    /**
     * Get and clear the steps.
     *
     * @return array
     */
    public function pullSteps()
    {
        $steps = $this->steps;

        $this->steps = [];

        return $steps;
    }

    /**
     * Get the steps to execute.
     *
     * @return array
     */
    public function getSteps()
    {
        return $this->steps;
    }
}
