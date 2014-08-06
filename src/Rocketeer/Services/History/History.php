<?php
namespace Rocketeer\Services\History;

use Illuminate\Support\Collection;

class History extends Collection
{
	/**
	 * Get the history, flattened
	 *
	 * @return array
	 */
	public function getFlattenedHistory()
	{
		return $this->getFlattened('history');
	}

	/**
	 * Get the output, flattened
	 *
	 * @return array
	 */
	public function getFlattenedOutput()
	{
		return $this->getFlattened('output');
	}

	/**
	 * Get a flattened list of a certain type
	 *
	 * @param string $type
	 *
	 * @return array
	 */
	protected function getFlattened($type)
	{
		$history = [];
		foreach ($this->items as $class => $entries) {
			$history = array_merge($history, $entries[$type]);
		}

		ksort($history);

		return array_values($history);
	}
}
