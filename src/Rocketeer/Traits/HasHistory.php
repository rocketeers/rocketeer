<?php
/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Rocketeer\Traits;

/**
 * A class that maintains an history of results/commands
 *
 * @author Maxime Fabre <ehtnam6@gmail.com>
 */
trait HasHistory
{
	/**
	 * Get the class's history
	 *
	 * @return array
	 */
	public function getHistory()
	{
		$handle = $this->getHistoryHandle();

		return $this->history[$handle];
	}

	/**
	 * Append an entry to the history
	 *
	 * @param array|string $command
	 */
	public function toHistory($command)
	{
		$this->appendTo('history', $command);
	}

	/**
	 * Append an entry to the output
	 *
	 * @param array|string $output
	 */
	public function toOutput($output)
	{
		$this->appendTo('output', $output);
	}

	/**
	 * Get the class's handle in the history
	 *
	 * @return string
	 */
	protected function getHistoryHandle()
	{
		$handle = get_called_class();

		// Create entry if it doesn't exist yet
		if (!isset($this->history[$handle])) {
			$this->history[$handle] = array(
				'history' => [],
				'output'  => [],
			);
		}

		return $handle;
	}

	/**
	 * Append something to the history
	 *
	 * @param string       $type
	 * @param string|array $command
	 */
	protected function appendTo($type, $command)
	{
		$handle    = $this->getHistoryHandle();
		$history   = $this->getHistory();
		$timestamp = (string) microtime(true);

		$history[$type][$timestamp] = $command;

		$this->history[$handle] = $history;
	}
}
