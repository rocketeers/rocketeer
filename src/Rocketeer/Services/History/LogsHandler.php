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

use Illuminate\Support\Arr;
use Rocketeer\Traits\HasLocator;

/**
 * Handles rotation of logs.
 */
class LogsHandler
{
    use HasLocator;

    /**
     * Cache of the logs file to be written.
     *
     * @var array
     */
    protected $logs = [];

    /**
     * The name of the logs file.
     *
     * @var string[]
     */
    protected $name = [];

    /**
     * Save something for the logs.
     *
     * @param string|string[] $string
     */
    public function log($string)
    {
        // Create entry in the logs
        $file = $this->getCurrentLogsFile();
        if (!isset($this->logs[$file])) {
            $this->logs[$file] = [];
        }

        // Prepend currenth handle
        $this->logs[$file][] = $this->prependHandle($string);
    }

    /**
     * Write the stored logs.
     *
     * @return array
     */
    public function write()
    {
        foreach ($this->logs as $file => $entries) {
            if (!$file) {
                continue;
            }

            // Create the file if it doesn't exist
            if (!$this->files->has($file)) {
                $this->createLogsFile($file);
            }

            $this->files->put($file, $this->formatEntries($entries));
        }

        return array_keys($this->logs);
    }

    //////////////////////////////////////////////////////////////////////
    //////////////////////////// CURRENT LOGS ////////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * Get the logs file being currently used.
     *
     * @return string|false
     */
    public function getCurrentLogsFile()
    {
        $hash = (string) $this->connections->getCurrentConnectionKey();
        if (array_key_exists($hash, $this->name)) {
            return $this->name[$hash];
        }

        // Get the namer closure
        $namer = $this->config->get('logs');
        $name = is_callable($namer) ? $namer($this->connections) : $namer;
        if (!$name) {
            return false;
        }

        // Save for reuse
        $name = $this->app->get('path.rocketeer.logs').'/'.$name;
        $this->name[$hash] = $name;

        return $name;
    }

    /**
     * Get the current logs.
     *
     * @return array
     */
    public function getLogs()
    {
        $current = (string) $this->getCurrentLogsFile();
        if (!$current) {
            return $this->logs[0];
        }

        return array_get($this->logs, $current);
    }

    /**
     * Get the current logs, flattened.
     *
     * @return string
     */
    public function getFlattenedLogs()
    {
        return $this->formatEntries($this->getLogs());
    }

    //////////////////////////////////////////////////////////////////////
    ////////////////////////////// HELPERS ///////////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * Create a logs file if it doesn't exist.
     *
     * @param string $file
     */
    protected function createLogsFile($file)
    {
        $directory = dirname($file);

        // Create directory
        if (!$this->files->isDirectory($directory)) {
            $this->files->createDir($directory);
        }

        // Create file
        if (!$this->files->has($file)) {
            $this->files->put($file, '');
        }
    }

    /**
     * Format entries to a string.
     *
     * @param array $entries
     *
     * @return string
     */
    protected function formatEntries($entries)
    {
        $entries = Arr::flatten($entries);
        $entries = implode(PHP_EOL, $entries);

        return $entries;
    }

    /**
     * Prepend the connection handle to each log entry.
     *
     * @param string|string[] $entries
     *
     * @return string|string[]
     */
    protected function prependHandle($entries)
    {
        $entries = (array) $entries;
        $handle = $this->connections->getCurrentConnectionKey()->toLongHandle();

        foreach ($entries as $key => $entry) {
            $entry = str_replace('<comment>['.$handle.']</comment> ', null, $entry);
            $entry = sprintf('[%s] %s', $handle, $entry);

            $entries[$key] = $entry;
        }

        return count($entries) === 1 ? $entries[0] : $entries;
    }
}
