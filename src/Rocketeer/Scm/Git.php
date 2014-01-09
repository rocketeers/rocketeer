<?php
/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Rocketeer\Scm;

use Rocketeer\Traits\Scm;

/**
 * The Git implementation of the ScmInterface
 *
 * @author Maxime Fabre <ehtnam6@gmail.com>
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
		$shallow    = $this->app['rocketeer.rocketeer']->getOption('scm.shallow') ? ' --depth 1' : '';

		return $this->getCommand('clone%s -b %s "%s" %s', $shallow, $branch, $repository, $destination);
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

	/**
	 * Checkout the repository's submodules
	 *
	 * @return string
	 */
	public function submodules()
	{
		return $this->getCommand('submodule update --init --recursive');
	}
}
