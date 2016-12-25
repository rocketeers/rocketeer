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

use Rocketeer\Traits\ContainerAwareTrait;

/**
 * Gives some insight into what task is executing,
 * what it's doing, what its parent is, etc.
 */
class QueueExplainer
{
    use ContainerAwareTrait;

    /**
     * The level at which to display statuses.
     *
     * @var int
     */
    public $level = 0;

    /**
     * @var int
     */
    protected $padding = 2;

    /**
     * Length of the longest handle to display.
     *
     * @var int
     */
    protected $longest;

    //////////////////////////////////////////////////////////////////////
    /////////////////////////////// STATUS ///////////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * Execute a task in a level below.
     *
     * @param callable $callback
     * @param int      $offset
     *
     * @return mixed
     */
    public function displayBelow(callable $callback, $offset = 1)
    {
        if (!$this->hasCommand()) {
            return $callback();
        }

        $this->level += $offset;
        $results = $callback();
        $this->level -= $offset;

        return $results;
    }

    /**
     * Display a status.
     *
     * @param string|null $info
     * @param string|null $details
     * @param string|null $origin
     * @param float|null  $time
     */
    public function display($info = null, $details = null, $origin = null, $time = null)
    {
        if (!$this->hasCommand()) {
            return;
        }

        // Build handle
        $comment = $this->getTree().$this->getFork();

        // Add details
        if ($info) {
            $comment .= ' <info>'.$info.'</info>';
        }
        if ($details) {
            $comment .= ' <comment>('.$details.')</comment>';
        }
        if ($origin) {
            $comment .= ' fired by <info>'.$origin.'</info>';
        }
        if ($time) {
            $comment .= ' [~'.$time.'s]';
        }

        $this->command->writeln($comment);
    }

    //////////////////////////////////////////////////////////////////////
    ////////////////////////////// PROGRESS //////////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * Format and send a message by the command.
     *
     * @param string|string[] $message
     * @param string|null     $color
     * @param bool            $withTree
     *
     * @return string|null
     */
    public function line($message, $color = null, $withTree = true)
    {
        $message = $this->toLines($message);
        if (!$this->hasCommand()) {
            return;
        }

        // Format the message
        $formatted = $message;
        foreach ($formatted as &$line) {
            $line = $this->colorize($line, $color);
            $line = $withTree ? $this->getTree().'|'.str_repeat(' ', $this->padding).$this->getFork().' '.$line : $line;
        }

        // Rejoin strings
        $formatted = implode(PHP_EOL, $formatted);
        $message = implode(PHP_EOL, $message);

        // Pass to command and log
        $this->command->writeln($formatted);
        $this->logs->log($message);

        return $formatted;
    }

    /**
     * Get the format for a progress bar
     * embedded within the tree.
     *
     * @param string $format
     *
     * @return string
     */
    public function getProgressBarFormat($format)
    {
        return $this->displayBelow(function () use (&$format) {
            $tree = $this->getTree().$this->getFork();

            $format = explode(PHP_EOL, $format);
            $format = $tree.implode(PHP_EOL.$tree.' ', $format);

            return $format;
        }, 2);
    }

    /**
     * Display a server-related message.
     *
     * @param string|string[] $message
     *
     * @return string|null
     */
    public function server($message)
    {
        $message = $this->toLines($message);
        foreach ($message as &$line) {
            $line = sprintf('<comment>[%s]</comment> %s', $this->connections->getCurrentConnectionKey()->toLongHandle(), $line);
        }

        return $this->line($message, null);
    }

    /**
     * @param string|string[] $message
     *
     * @return string|null
     */
    public function success($message)
    {
        return $this->line($message, 'green');
    }

    /**
     * @param string|string[] $message
     *
     * @return string|null
     */
    public function comment($message)
    {
        return $this->line($message, 'comment');
    }

    /**
     * @param string|string[] $message
     *
     * @return string|null
     */
    public function info($message)
    {
        return $this->line($message, 'info');
    }

    /**
     * @param string|string[] $message
     *
     * @return string|null
     */
    public function error($message)
    {
        return $this->line($message, 'error');
    }

    //////////////////////////////////////////////////////////////////////
    ////////////////////////////// HELPERS ///////////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * Get the longest size an handle can have.
     *
     * @return int
     */
    protected function getLongestSize()
    {
        if ($this->longest) {
            return $this->longest;
        }

        // Build possible handles
        $strings = [];
        $connections = $this->connections->getActiveConnections();
        $stages = (array) $this->connections->getAvailableStages() ?: [''];
        foreach ($connections as $handle => $connection) {
            $servers = $connection->getConnectionKey()->servers;
            if (count($servers) > 1) {
                $handle .= '/'.count($servers);
            }

            foreach ($stages as $stage) {
                $strings[] = trim($handle.'/'.$stage, '/');
            }
        }

        // Get longest string
        $strings = array_map('strlen', $strings);
        $strings = $strings ? max($strings) : 0;

        // Cache value
        $this->longest = $strings;

        return $this->longest;
    }

    /**
     * @param string $dashes
     *
     * @return string
     */
    protected function getTree($dashes = null)
    {
        // Build handle
        $dashes = $dashes ?: '│'.str_repeat(' ', $this->padding);
        $numberConnections = count($this->connections->getActiveConnections());
        $numberStages = count($this->connections->getAvailableStages());
        $numberServers = count($this->connections->getCurrentConnectionKey()->servers);

        $tree = null;
        if ($numberConnections > 1 || $numberStages > 1 || $numberServers > 1) {
            $handle = $this->connections->getCurrentConnectionKey()->toHandle();
            $spacing = $this->getLongestSize() - mb_strlen($handle) + 2;
            $spacing = max(1, $spacing);
            $spacing = str_repeat(' ', $spacing);

            // Build tree and command
            $handle = $handle === 'dummy' ? 'local' : $handle;
            $spacing = mb_substr($spacing, mb_strlen($spacing) / 2);
            $tree .= sprintf('<bg=magenta;options=bold>%s%s%s</bg=magenta;options=bold> ', $spacing, $handle, $spacing);
        }

        // Add tree
        $dashes = $this->level ? str_repeat($dashes, $this->level) : null;
        $tree .= $dashes;

        return $tree;
    }

    /**
     * @return string
     */
    protected function getFork()
    {
        return '├'.str_repeat('─', $this->padding - 1);
    }

    /**
     * Colorize text using Symfony Console tags.
     *
     * @param string      $message
     * @param string|null $color
     *
     * @return string
     */
    protected function colorize($message, $color = null)
    {
        if (!$color) {
            return $message;
        }

        // Create tag
        $tag = in_array($color, ['error', 'comment', 'info'], true) ? $color : 'fg='.$color;

        return sprintf('<%s>%s</%s>', $tag, $message, $tag);
    }

    /**
     * @param string|string[] $message
     *
     * @return array
     */
    protected function toLines($message)
    {
        return is_array($message) ? $message : explode(PHP_EOL, $message);
    }
}
