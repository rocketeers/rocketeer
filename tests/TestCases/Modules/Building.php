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

namespace Rocketeer\TestCases\Modules;

/**
 * @mixin \Rocketeer\TestCases\RocketeerTestCase
 *
 * @author Maxime Fabre <ehtnam6@gmail.com>
 */
trait Building
{
    /**
     * Get a pretend AbstractTask to run bogus commands.
     *
     * @param string $task
     * @param array  $options
     *
     * @return \Rocketeer\Tasks\AbstractTask
     */
    protected function pretendTask($task = 'Deploy', $options = [])
    {
        $this->pretend($options);

        return $this->task($task);
    }

    /**
     * Get AbstractTask instance.
     *
     * @param string $task
     * @param array  $options
     *
     * @return \Rocketeer\Tasks\AbstractTask
     */
    protected function task($task = null, $options = [])
    {
        if ($options) {
            $this->mockCommand($options);
        }

        if (!$task) {
            return $this->task;
        }

        return $this->builder->buildTask($task);
    }
}
