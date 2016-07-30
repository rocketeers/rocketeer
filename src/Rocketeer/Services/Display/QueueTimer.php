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

namespace Rocketeer\Services\Display;

use Rocketeer\Interfaces\IdentifierInterface;
use Rocketeer\Traits\ContainerAwareTrait;

/**
 * Saves the execution time of tasks and
 * predicts their future ones.
 */
class QueueTimer
{
    use ContainerAwareTrait;

    //////////////////////////////////////////////////////////////////////
    /////////////////////////////// TASKS ////////////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * Time a task operation.
     *
     * @param IdentifierInterface $entity
     * @param callable            $callback
     *
     * @return mixed
     */
    public function time(IdentifierInterface $entity, callable $callback)
    {
        // Start timer, execute callback, close timer
        $timerStart = microtime(true);
        $results = $callback();
        $time = round(microtime(true) - $timerStart, 4);

        $this->saveTime($entity, $time);

        return $results;
    }

    /**
     * Save the execution time of a task for future reference.
     *
     * @param IdentifierInterface $entity
     * @param float               $time
     */
    public function saveTime(IdentifierInterface $entity, $time)
    {
        // Don't save times in pretend mode
        if ($this->getOption('pretend')) {
            return;
        }

        // Append the new time to past ones
        $past = $this->getTimes($entity);
        $past[] = $time;

        $this->saveTimes($entity, $past);
    }

    /**
     * Compute the predicted execution time of a task.
     *
     * @param IdentifierInterface $entity
     *
     * @return float|null
     */
    public function getTime(IdentifierInterface $entity)
    {
        $past = $this->getTimes($entity);
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
     * @param IdentifierInterface $entity
     *
     * @return array
     */
    public function getTimes(IdentifierInterface $entity)
    {
        $handle = $this->getTimerHandle($entity);
        $past = $this->localStorage->get($handle, []);

        return $past;
    }

    /**
     * Get the last recorded time.
     *
     * @param IdentifierInterface $entity
     *
     * @return float|null
     */
    public function getLatestTime(IdentifierInterface $entity)
    {
        $times = $this->getTimes($entity);

        return end($times);
    }

    /**
     * @param IdentifierInterface $entity
     * @param float[]             $past
     */
    public function saveTimes(IdentifierInterface $entity, array $past)
    {
        $handle = $this->getTimerHandle($entity);

        $this->localStorage->set($handle, $past);
    }

    //////////////////////////////////////////////////////////////////////
    ////////////////////////////// HELPERS ///////////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * @param IdentifierInterface $entity
     *
     * @return string
     */
    protected function getTimerHandle(IdentifierInterface $entity)
    {
        return sprintf('times.%s', $entity->getIdentifier());
    }
}
