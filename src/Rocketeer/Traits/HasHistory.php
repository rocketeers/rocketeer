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

use Illuminate\Support\Arr;

/**
 * A class that maintains an history of results/commands.
 *
 * @property \Rocketeer\Services\History\History history
 *
 * @author Maxime Fabre <ehtnam6@gmail.com>
 */
trait HasHistory
{
    /**
     * Get the class's history.
     *
     * @param string|null $type
     *
     * @return array
     */
    public function getHistory($type = null)
    {
        $handle  = $this->getHistoryHandle();
        $history = $this->history[$handle];
        $history = Arr::get($history, $type);

        return $history;
    }

    /**
     * Append an entry to the history.
     *
     * @param array|string|bool $command
     */
    public function toHistory($command)
    {
        $this->appendTo('history', $command);
    }

    /**
     * Append an entry to the output.
     *
     * @param array|string|bool $output
     */
    public function toOutput($output)
    {
        $this->appendTo('output', $output);
    }

    /**
     * Get the class's handle in the history.
     *
     * @return string
     */
    protected function getHistoryHandle()
    {
        $handle = get_called_class();

        // Create entry if it doesn't exist yet
        if (!isset($this->history[$handle])) {
            $this->history[$handle] = [
                'history' => [],
                'output'  => [],
            ];
        }

        return $handle;
    }

    /**
     * Append something to the history.
     *
     * @param string            $type
     * @param string|array|bool $command
     */
    protected function appendTo($type, $command)
    {
        // Flatten one-liners
        $command   = (array) $command;
        $command   = array_values($command);
        $flattened = count($command) === 1 ? $command[0] : $command;

        // Save to logs
        if ($type === 'history') {
            $command = array_map(function ($command) {
                return '$ '.$command;
            }, $command);
        }

        $this->logs->log($command);

        // Get the various handles
        $handle    = $this->getHistoryHandle();
        $history   = $this->getHistory();
        $timestamp = (string) microtime(true);

        // Set new history on correct handle
        $history[$type][$timestamp] = $flattened;

        $this->history[$handle] = $history;
    }
}
