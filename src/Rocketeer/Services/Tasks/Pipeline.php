<?php

/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Rocketeer\Services\Tasks;

use Illuminate\Support\Collection;

/**
 * A class representing a pipeline of jobs
 * to be executed.
 *
 * @author Maxime Fabre <ehtnam6@gmail.com>
 */
class Pipeline extends Collection
{
    /**
     * The stored results of each task.
     *
     * @type array
     */
    protected $results = [];

    //////////////////////////////////////////////////////////////////////
    ////////////////////////////// RESULTS ///////////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * Check if the pipeline failed.
     *
     * @return bool
     */
    public function failed()
    {
        $succeeded = count(array_filter($this->results));

        return $succeeded !== $this->count();
    }

    /**
     * Check if the pipeline ran its course.
     *
     * @return bool
     */
    public function succeeded()
    {
        return !$this->failed();
    }

    /**
     * @return array
     */
    public function getResults()
    {
        return $this->results;
    }

    /**
     * @param array $results
     */
    public function setResults($results)
    {
        $this->results = $results;
    }
}
