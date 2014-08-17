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
	 * The level at which to display statuses
	 *
	 * @type integer
	 */
	public $level = 0;

	/**
	 * Length of the longest handle to display
	 *
	 * @type integer
	 */
	protected $longest;

	//////////////////////////////////////////////////////////////////////
	/////////////////////////////// STATUS ///////////////////////////////
	//////////////////////////////////////////////////////////////////////

	/**
	 * Execute a task in a level below
	 *
	 * @param Closure $callback
	 *
	 * @return mixed
	 */
	public function displayBelow(Closure $callback)
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
	 * Display a status
	 *
	 * @param string      $object
	 * @param string      $subject
	 * @param string|null $details
	 * @param float|null  $time
	 */
	public function display($object, $subject = null, $details = null, $time = null)
	{
		if (!$this->hasCommand()) {
			return;
		}

		// Build handle
		$comment = $this->getTree().' '.$object;

		// Add details
		if ($subject) {
			$comment .= ': <info>'.$subject.'</info>';
		}
		if ($details) {
			$comment .= ' <comment>('.$details.')</comment>';
		}
		if ($time) {
			$comment .= ' [~'.$time.'s]';
		}

		$this->command->line($comment);
	}

	/**
	 * Display the results of something
	 *
	 * @param string $comment
	 *
	 * @return string
	 */
	public function results($comment)
	{
		if (!$this->hasCommand()) {
			return;
		}

		// Build results and display them
		$comment = $this->getTree('==').'=> '.$comment;
		$this->command->line($comment);

		return $comment;
	}

	//////////////////////////////////////////////////////////////////////
	////////////////////////////// HELPERS ///////////////////////////////
	//////////////////////////////////////////////////////////////////////

	/**
	 * Get the longest size an handle can have
	 *
	 * @return integer
	 */
	protected function getLongestSize()
	{
		if ($this->longest) {
			return $this->longest;
		}

		// Build possible handles
		$strings     = [];
		$connections = (array) $this->connections->getAvailableConnections();
		$stages      = (array) $this->connections->getStages();
		foreach ($connections as $connection => $servers) {
			foreach ($stages as $stage) {
				$strings[] = $connection.'/' .sizeof($servers). '/'.$stage;
			}
		}

		// Get longest string
		$strings = array_map('strlen', $strings);
		$strings = $strings ? max($strings) : 0;

		// Cache value
		$this->longest = $strings + 1;

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
		$handle  = $this->connections->getHandle();
		$spacing = $this->getLongestSize() - strlen($handle);
		$spacing = $spacing < 1 ? 1 : $spacing;
		$spacing = str_repeat(' ', $spacing);

		// Build tree and command
		$dashes = $this->level ? str_repeat($dashes, $this->level) : null;
		$tree   = sprintf('<fg=cyan>%s</fg=cyan>%s|%s', $handle, $spacing, $dashes);

		return $tree;
	}
}
