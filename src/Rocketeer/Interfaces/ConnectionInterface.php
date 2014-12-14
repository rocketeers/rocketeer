<?php
namespace Rocketeer\Interfaces;

interface ConnectionInterface extends \Illuminate\Remote\ConnectionInterface
{
	/**
	 * Get the exit status of the last command.
	 *
	 * @return integer|bool
	 */
	public function status();

	/**
	 * Display the given line using the default output.
	 *
	 * @param string $line
	 *
	 * @return void
	 */
	public function display($line);
}
