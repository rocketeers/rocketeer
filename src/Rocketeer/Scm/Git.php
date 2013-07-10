<?php
namespace Rocketeer\Scm;

use Illuminate\Container\Container;

/**
 * The Git SCM
 */
class Git implements Scm
{

	/**
	 * The IoC Container
	 *
	 * @var Container
	 */
	protected $app;

	/**
	 * The core binary
	 *
	 * @var string
	 */
	public $binary = 'git';

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
	///////////////////////////// INFORMATIONS /////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Check if the SCM is available
	 *
	 * @return string
	 */
	public function check()
	{
		return $this->binary. ' --version';
	}

	/**
	 * Get the current state
	 *
	 * @return string
	 */
	public function currentState()
	{
		return $this->binary. ' rev-parse HEAD';
	}

	/**
	 * Get the current branch
	 *
	 * @return string
	 */
	public function currentBranch()
	{
		return $this->binary. ' rev-parse --abbrev-ref HEAD';
	}

	////////////////////////////////////////////////////////////////////
	/////////////////////////////// ACTIONS ////////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Clone a repository
	 *
	 * @param  string $destination
	 *
	 * @return string
	 */
	public function checkout($destination)
	{
		$branch     = $this->app['rocketeer.rocketeer']->getRepositoryBranch();
		$repository = $this->app['rocketeer.rocketeer']->getRepository();

		return sprintf($this->binary. ' clone -b %s %s %s', $branch, $repository, $destination);
	}

	/**
	 * Resets the repository
	 *
	 * @return string
	 */
	public function reset()
	{
		return $this->binary. ' reset --hard';
	}

	/**
	 * Updates the repository
	 *
	 * @return string
	 */
	public function update()
	{
		return $this->binary. ' pull';
	}
}
