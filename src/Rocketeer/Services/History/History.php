<?php

/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Rocketeer\Services\History;

use Illuminate\Support\Collection;

class History extends Collection
{
    /**
     * Get the history, flattened.
     *
     * @return string[]|string[][]
     */
    public function getFlattenedHistory()
    {
        return $this->getFlattened('history');
    }

    /**
     * Get the output, flattened.
     *
     * @return string[]|string[][]
     */
    public function getFlattenedOutput()
    {
        return $this->getFlattened('output');
    }

    //////////////////////////////////////////////////////////////////////
    ////////////////////////////// HELPERS ///////////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * Get a flattened list of a certain type.
     *
     * @param string $type
     *
     * @return string[]|string[][]
     */
    protected function getFlattened($type)
    {
        $history = [];
        foreach ($this->items as $class => $entries) {
            $history = array_merge($history, $entries[$type]);
        }

        ksort($history);

        return array_values($history);
    }
}
