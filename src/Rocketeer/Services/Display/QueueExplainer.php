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
	public $level = 1;

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
	public function display($object, $subject, $details = null, $time = null)
	{
		if (!$this->hasCommand()) {
			return;
		}

		// Build status
		$tree    = '|'.str_repeat('--', $this->level);
		$comment = sprintf('%s %s: <info>%s</info>', $tree, $object, $subject);

		// Add details
		if ($details) {
			$comment .= ' <comment>('.$details.')</comment>';
		}
		if ($time) {
			$comment .= ' [~'.$time.'s]';
		}

		$this->command->line($comment);
	}
}
