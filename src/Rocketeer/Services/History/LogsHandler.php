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
     * @type array
     */
    protected $logs = [];

    /**
     * The name of the logs file.
     *
     * @type string[]
     */
    protected $name = [];

    /**
     * Save something for the logs.
     *
     * @param string $string
     */
    public function log($string)
    {
        // Create entry in the logs
        $file = $this->getCurrentLogsFile();
        if (!isset($this->logs[$file])) {
            $this->logs[$file] = [];
        }

        $this->logs[$file][] = $string;
    }

    /**
     * Write the stored logs.
     *
     * @return array
     */
    public function write()
    {
        foreach ($this->logs as $file => $entries) {
            $entries = Arr::flatten($entries);
            if (!$this->files->exists($file)) {
                $this->createLogsFile($file);
            }

            $this->files->put($file, implode(PHP_EOL, $entries));
        }

        return array_keys($this->logs);
    }

    /**
     * Get the logs file being currently used.
     *
     * @return string|false
     */
    public function getCurrentLogsFile()
    {
        $hash = $this->connections->getHandle();
        if (array_key_exists($hash, $this->name)) {
            return $this->name[$hash];
        }

        // Get the namer closure
        $namer = $this->config->get('rocketeer::logs');

        // Cancel if invalid namer
        if (!$namer || !is_callable($namer)) {
            return false;
        }

        // Compute name
        $name = $namer($this->connections);
        $name = $this->app['path.rocketeer.logs'].'/'.$name;

        // Save for reuse
        $this->name[$hash] = $name;

        return $name;
    }

    /**
     * Create a logs file if it doesn't exist.
     *
     * @param string $file
     */
    protected function createLogsFile($file)
    {
        $directory = dirname($file);

        // Create directory
        if (!is_dir($directory)) {
            $this->files->makeDirectory($directory, 0777, true);
        }

        // Create file
        if (!file_exists($file)) {
            $this->files->put($file, '');
        }
    }
}
