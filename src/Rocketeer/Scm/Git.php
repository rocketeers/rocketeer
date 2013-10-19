<?php
namespace Rocketeer\Scm;

use Rocketeer\Traits\Scm;

/**
 * The Git SCM
 */
class Git extends Scm implements ScmInterface
{
	/**
	 * The core binary
	 *
	 * @var string
	 */
	public $binary = 'git';

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
		return $this->getCommand('--version');
	}

	/**
	 * Get the current state
	 *
	 * @return string
	 */
	public function currentState()
	{
		return $this->getCommand('rev-parse HEAD');
	}

	/**
	 * Get the current branch
	 *
	 * @return string
	 */
	public function currentBranch()
	{
		return $this->getCommand('rev-parse --abbrev-ref HEAD');
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

		return sprintf($this->getCommand('clone --depth 1 -b %s %s %s'), $branch, $repository, $destination);
	}

	/**
	 * Resets the repository
	 *
	 * @return string
	 */
	public function reset()
	{
		return $this->getCommand('reset --hard');
	}

	/**
	 * Updates the repository
	 *
	 * @return string
	 */
	public function update()
	{
		return $this->getCommand('pull');
	}
}
