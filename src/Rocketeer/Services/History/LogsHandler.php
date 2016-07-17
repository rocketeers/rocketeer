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

namespace Rocketeer\Services\History;

use Illuminate\Support\Arr;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\NullHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use Rocketeer\Traits\ContainerAwareTrait;

/**
 * Handles rotation of logs.
 */
class LogsHandler
{
    use ContainerAwareTrait;

    /**
     * The name of the logs file.
     *
     * @var LoggerInterface[]
     */
    protected $loggers = [];

    /**
     * Save something for the logs.
     *
     * @param string|string[] $message
     */
    public function log($message)
    {
        $messages = (array) $message;
        foreach ($messages as $message) {
            $this->getLogger()->info($this->prependHandle($message));
        }
    }

    ////////////////////////////////////////////////////////////////////////////////
    /////////////////////////////////// FACTORY ////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////

    /**
     * @return LoggerInterface
     */
    protected function getLogger()
    {
        // Cache logger
        $channel = $this->connections->getCurrentConnectionKey()->toLongHandle();
        if (array_key_exists($channel, $this->loggers)) {
            return $this->loggers[$channel];
        }

        $logger = new Logger($channel);

        // If the filename is invalid, log to null
        if (!$realpath = $this->getLogsRealpath()) {
            $handler = new NullHandler();
        } else {
            // Else create logs file and handler
            $adapter = $this->files->getAdapter();
            $prefixed = $adapter ? $adapter->applyPathPrefix($realpath) : $realpath;

            $this->createLogsFile($realpath);
            $handler = new StreamHandler($prefixed);
        }

        // Set formatter and handler on Logger
        $handler->setFormatter(new LineFormatter('[%datetime%] %channel%: %message%'.PHP_EOL));
        $logger->pushHandler($handler);
        $this->loggers[$channel] = $logger;

        return $logger;
    }

    /**
     * Get the logs file being currently used.
     *
     * @return string|false
     */
    public function getLogsRealpath()
    {
        // Get the namer closure
        $namer = $this->config->get('logs');
        $name = is_callable($namer) ? $namer($this->connections) : $namer;
        if (!$name) {
            return false;
        }

        // Save for reuse
        $name = $this->paths->getLogsPath().DS.$name;

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
        if (!$this->files->isDirectory($directory)) {
            $this->files->createDir($directory);
        }

        // Create file
        if (!$this->files->has($file)) {
            $this->files->put($file, '');
        }
    }

    ////////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////// RETRIEVAL ///////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////

    /**
     * Get the current logs.
     *
     * @return array
     */
    public function getLogs()
    {
        $realpath = (string) $this->getLogsRealpath();
        if (!$realpath) {
            return [];
        }

        $logs = trim($this->files->read($realpath));
        $logs = explode(PHP_EOL, $logs);

        return $logs;
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
        $entries = trim($entries);

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

        foreach ($entries as &$entry) {
            $entry = str_replace('<comment>['.$handle.']</comment> ', null, $entry);
        }

        return count($entries) === 1 ? $entries[0] : $entries;
    }
}
