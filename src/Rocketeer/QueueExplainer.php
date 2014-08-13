<?php
namespace Rocketeer;

use Closure;
use Rocketeer\Traits\HasLocator;

class QueueExplainer
{
	use HasLocator;

	/**
	 * The level at which to display statuses
	 *
	 * @type integer
	 */
	public $level = 2;

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
		if (!$this->command) {
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
	 */
	public function display($object, $subject, $details = null)
	{
		if (!$this->command) {
			return;
		}

		// Build status
		$tree    = str_repeat('-', $this->level);
		$comment = sprintf('%s %s: <info>%s</info>', $tree, $object, $subject);
		if ($details) {
			$comment .= ' <comment>('.$details.')</comment>';
		}

		$this->command->line($comment);
	}
}
