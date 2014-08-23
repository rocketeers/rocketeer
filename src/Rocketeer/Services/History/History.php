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
use Illuminate\Support\Collection;

class History extends Collection
{
	/**
	 * Get the history, flattened
	 *
	 * @return string[]|string[][]
	 */
	public function getFlattenedHistory()
	{
		return $this->getFlattened('history');
	}

	/**
	 * Get the output, flattened
	 *
	 * @return string[]|string[][]
	 */
	public function getFlattenedOutput()
	{
		return $this->getFlattened('output');
	}

	/**
	 * Get the merged logs of history/output
	 *
	 * @return array
	 */
	public function getLogs()
	{
		// Fetch history
		$history = $this->getFlattened('history', true);
		$history = Arr::dot($history);

		// Fetch output
		$output = $this->getFlattened('output', true);
		$output = Arr::dot($output);

		// Add command marker to history
		$history = array_map(function ($command) {
			return '$ '.$command;
		}, $history);

		// Merge and sort
		$logs = array_merge($history, $output);
		ksort($logs);

		return array_values($logs);
	}

	//////////////////////////////////////////////////////////////////////
	////////////////////////////// HELPERS ///////////////////////////////
	//////////////////////////////////////////////////////////////////////

	/**
	 * Get a flattened list of a certain type
	 *
	 * @param string  $type
	 * @param boolean $timestamps
	 *
	 * @return string[]|string[][]
	 */
	protected function getFlattened($type, $timestamps = false)
	{
		$history = [];
		foreach ($this->items as $class => $entries) {
			$history = array_merge($history, $entries[$type]);
		}

		ksort($history);

		// Prune timestamps if necessary
		if (!$timestamps) {
			$history = array_values($history);
		}

		return $history;
	}
}
