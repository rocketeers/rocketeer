<?php
/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Rocketeer\Abstracts;

use Illuminate\Support\Str;
use Rocketeer\Traits\HasLocator;

/**
 * A generic class to represent a binary as a class
 *
 * @author Maxime Fabre <ehtnam6@gmail.com>
 */
class AbstractBinary
{
	use HasLocator;

	/**
	 * The core binary
	 *
	 * @var string
	 */
	protected $binary;

	/**
	 * Get the current binary name
	 *
	 * @return string
	 */
	public function getBinary()
	{
		return $this->binary;
	}

	/**
	 * Execute a command on the Binary
	 *
	 * @param string $name
	 * @param array  $arguments
	 *
	 * @return string|null
	 */
	public function __call($name, $arguments)
	{
		// Check whether we want the command executed or not
		$execute = strpos($name, 'execute') !== false;
		$name    = str_replace('execute', null, $name);

		// Format name
		$name = Str::snake($name, '-');

		// Prepend command name to arguments and call
		array_unshift($arguments, $name);
		$command = call_user_func_array([$this, 'getCommand'], $arguments);

		return $execute ? $this->bash->run($command) : $command;
	}

	/**
	 * Execute one of the commands
	 *
	 * @return string|null
	 */
	public function execute()
	{
		$arguments = func_get_args();
		$command   = array_shift($arguments);
		$command   = call_user_func_array(array($this, $command), $arguments);

		return $this->bash->run($command);
	}

	////////////////////////////////////////////////////////////////////
	//////////////////////////////// HELPERS ///////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Returns a command with the SCM's binary
	 *
	 * @param string   $command
	 * @param string[] $arguments
	 * @param string[] $flags
	 *
	 * @return string
	 */
	public function getCommand($command, $arguments = array(), $flags = array())
	{
		// Format arguments
		$arguments = $this->buildArguments($arguments);
		$options   = $this->buildOptions($flags);

		// Build command
		$command = $this->binary.' '.$command;
		if ($arguments) {
			$command .= ' '.$arguments;
		}
		if ($options) {
			$command .= ' '.$options;
		}

		return trim($command);
	}

	/**
	 * @param string[] $flags
	 *
	 * @return string
	 */
	protected function buildOptions($flags)
	{
		$options = [];
		$flags   = (array) $flags;

		// Flip array if necessary
		$firstKey = array_get(array_keys($flags), 0);
		if (!is_null($firstKey) and is_int($firstKey)) {
			$flags = array_combine(
				array_values($flags),
				array_fill(0, count($flags), null)
			);
		}

		// Build flags
		foreach ($flags as $flag => $value) {
			$options[] = $value ? $flag.'="'.$value.'"' : $flag;
		}

		return implode(' ', $options);
	}

	/**
	 * @param string[] $arguments
	 *
	 * @return string
	 */
	protected function buildArguments($arguments)
	{
		$arguments = (array) $arguments;
		$arguments = implode(' ', $arguments);

		return $arguments;
	}

	/**
	 * Quote a string
	 *
	 * @param string $string
	 *
	 * @return string
	 */
	protected function quote($string)
	{
		return '"'.$string.'"';
	}
}
