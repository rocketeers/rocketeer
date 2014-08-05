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

use Rocketeer\Traits\HasLocator;

/**
 * An abstract class with helpers for SCM implementations
 *
 * @author Maxime Fabre <ehtnam6@gmail.com>
 */
abstract class Scm
{
	use HasLocator;

	/**
	 * The core binary
	 *
	 * @var string
	 */
	public $binary;

	////////////////////////////////////////////////////////////////////
	//////////////////////////////// HELPERS ///////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Returns a command with the SCM's binary
	 *
	 * @param string $commands,...
	 *
	 * @return string
	 */
	public function getCommand()
	{
		$arguments    = func_get_args();
		$arguments[0] = $this->binary.' '.$arguments[0];

		return call_user_func_array('sprintf', $arguments);
	}

	/**
	 * Execute one of the commands
	 *
	 * @param  string $command
	 * @param  string $arguments,...
	 *
	 * @return string|string[]
	 */
	public function execute()
	{
		$arguments = func_get_args();
		$command   = array_shift($arguments);
		$command   = call_user_func_array(array($this, $command), $arguments);

		return $this->bash->run($command);
	}
}
