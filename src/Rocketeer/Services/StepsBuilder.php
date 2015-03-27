<?php

/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Rocketeer\Services;

/**
 * Saves in an array methods the call signatures
 * of the methods called on it.
 *
 * @author Maxime Fabre <ehtnam6@gmail.com>
 */
class StepsBuilder
{
    /**
     * The extisting steps.
     *
     * @type array
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
        $this->steps[] = [$name, $arguments];
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
