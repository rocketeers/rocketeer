<?php
namespace Rocketeer\History;

use Illuminate\Support\Collection;

class History extends Collection
{
	/**
	 * Get the history, flattened
	 */
	public function getFlattened()
	{
		$history = [];
		foreach ($this->items as $class => $entries) {
			$history = array_merge($history, $entries);
		}

		ksort($history);

		return array_values($history);
	}
}
