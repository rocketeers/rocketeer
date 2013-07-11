<?php
namespace Rocketeer\Traits;

/**
 * A base trait for SCM classes
 */
abstract class Scm
{
	/**
	 * The IoC Container
	 *
	 * @var Container
	 */
	protected $app;

	/**
	 * Build a new Git instance
	 *
	 * @param Container $app
	 */
	public function __construct($app)
	{
		$this->app = $app;
	}

	////////////////////////////////////////////////////////////////////
	//////////////////////////////// HELPERS ///////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Returns a command with the SCM's binary
	 *
	 * @param  string $command
	 *
	 * @return string
	 */
	public function getCommand($command)
	{
		return $this->binary. ' ' .$command;
	}
}
