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

use Rocketeer\Traits\HasLocator;

/**
 * Gives some insight into what task is executing,
 * what it's doing, what its parent is, etc.
 *
 * @author Maxime Fabre <ehtnam6@gmail.com>
 */
class QueueExplainer
{
    use HasLocator;

    /**
     * The level at which to display statuses.
     *
     * @type int
     */
    public $level = 0;

    /**
     * Length of the longest handle to display.
     *
     * @type int
     */
    protected $longest;

    //////////////////////////////////////////////////////////////////////
    /////////////////////////////// STATUS ///////////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * Execute a task in a level below.
     *
     * @param callable $callback
     *
     * @return mixed
     */
    public function displayBelow(callable $callback)
    {
        if (!$this->hasCommand()) {
            return $callback();
        }

        $this->level++;
        $results = $callback();
        $this->level--;

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
        $comment = $this->getTree();

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

        $this->command->line($comment);
    }

    //////////////////////////////////////////////////////////////////////
    ////////////////////////////// PROGRESS //////////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * Format and send a message by the command.
     *
     * @param string      $message
     * @param string|null $color
     * @param bool        $withTree
     *
     * @return string|null
     */
    public function line($message, $color = null, $withTree = true)
    {
        if (!$this->hasCommand()) {
            return;
        }

        // Format the message
        $formatted = $this->colorize($message, $color);
        $formatted = $withTree ? $this->getTree('==').'=> '.$formatted : $formatted;

        // Pass to command and log
        $this->command->line($formatted);
        $this->logs->log($message);

        return $formatted;
    }

    /**
     * Display a server-related message.
     *
     * @param string $message
     *
     * @return string|null
     */
    public function server($message)
    {
        $message = sprintf('<comment>[%s]</comment> %s', $this->connections->getCurrentConnection()->toLongHandle(), $message);

        return $this->line($message, null, false);
    }

    /**
     * @param string $message
     *
     * @return string|null
     */
    public function success($message)
    {
        return $this->line($message, 'green');
    }

    /**
     * @param string $message
     *
     * @return string|null
     */
    public function comment($message)
    {
        return $this->line($message, 'comment');
    }

    /**
     * @param string $message
     *
     * @return string|null
     */
    public function info($message)
    {
        return $this->line($message, 'info');
    }

    /**
     * @param string $message
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
        $strings     = [];
        $connections = (array) $this->connections->getAvailableConnections();
        $stages      = (array) $this->connections->getAvailableStages();
        foreach ($connections as $connection => $servers) {
            foreach ($stages as $stage) {
                $strings[] = $connection.'/'.count($servers).'/'.$stage;
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
    protected function getTree($dashes = '--')
    {
        // Build handle
        $numberConnections = count($this->connections->getAvailableConnections());
        $numberStages      = count($this->connections->getAvailableStages());

        $tree = null;
        if ($numberConnections > 1 || $numberStages > 1) {
            $handle  = $this->connections->getCurrentConnection();
            $spacing = $this->getLongestSize() - strlen($handle);
            $spacing = max(1, $spacing);
            $spacing = str_repeat(' ', $spacing);

            // Build tree and command
            $tree .= sprintf('<fg=cyan>%s</fg=cyan>%s', $handle, $spacing);
        }

        // Add tree
        $dashes = $this->level ? str_repeat($dashes, $this->level) : null;
        $tree .= '|'.$dashes;

        return $tree;
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
}
