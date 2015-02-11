<?php
namespace Rocketeer\Console\Compilation;

class RocketeerCompiler
{
	/**
	 * @type Compiler
	 */
	protected $compiler;

	/**
	 * Build a new Rocketeer PHAR compiler
	 */
	public function __construct()
	{
		$this->compiler = new Compiler(__DIR__.'/../../../../bin', 'rocketeer', array(
			'herrera-io',
			'johnkary',
			'mockery',
			'nesbot',
			'phine',
		));
	}

	/**
	 * Delegate calls to the Compiler
	 *
	 * @param string $name
	 * @param array  $arguments
	 *
	 * @return mixed
	 */
	public function __call($name, $arguments)
	{
		return call_user_func_array([$this->compiler, $name], $arguments);
	}
}
