<?php

/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Rocketeer\Services\Display;

use Closure;
use Rocketeer\Abstracts\AbstractTask;
use Rocketeer\Traits\HasLocator;

/**
 * Saves the execution time of tasks and
 * predicts their future ones.
 *
 * @author Maxime Fabre <ehtnam6@gmail.com>
 */
class QueueTimer
{
    use HasLocator;

    /**
     * Time a task operation.
     *
     * @param AbstractTask $task
     * @param Closure      $callback
     *
     * @return bool|null
     */
    public function time(AbstractTask $task, Closure $callback)
    {
        // Start timer, execute callback, close timer
        $timerStart = microtime(true);
        $callback();
        $time = round(microtime(true) - $timerStart, 4);

        $this->saveTaskTime($task, $time);
    }

    /**
     * Save the execution time of a task for future reference.
     *
     * @param AbstractTask $task
     * @param float        $time
     */
    public function saveTaskTime(AbstractTask $task, $time)
    {
        // Don't save times in pretend mode
        if ($this->getOption('pretend')) {
            return;
        }

        // Append the new time to past ones
        $past   = $this->getTaskTimes($task);
        $past[] = $time;

        $this->saveTaskTimes($task, $past);
    }

    /**
     * Compute the predicted execution time of a task.
     *
     * @param AbstractTask $task
     *
     * @return float|null
     */
    public function getTaskTime(AbstractTask $task)
    {
        $past = $this->getTaskTimes($task);
        if (!$past) {
            return;
        }

        // Compute average time
        $average = array_sum($past) / count($past);
        $average = round($average, 2);

        return $average;
    }

    //////////////////////////////////////////////////////////////////////
    ////////////////////////// SETTERS/GETTERS ///////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * @param AbstractTask $task
     *
     * @return array
     */
    protected function getTaskTimes(AbstractTask $task)
    {
        $handle = sprintf('times.%s', $task->getSlug());
        $past   = $this->localStorage->get($handle, []);

        return $past;
    }

    /**
     * @param AbstractTask $task
     * @param double[]     $past
     */
    protected function saveTaskTimes(AbstractTask $task, array $past)
    {
        $handle = sprintf('times.%s', $task->getSlug());
        $this->localStorage->set($handle, $past);
    }
}
