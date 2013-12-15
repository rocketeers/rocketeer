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
 * The Svn implementation of the ScmInterface
 *
 * @author Maxime Fabre <ehtnam6@gmail.com>
 * @author Gasillo
 */
class Svn extends Scm implements ScmInterface
{
	/**
	 * The core binary
	 *
	 * @var string
	 */
	public $binary = 'svn';

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
		return $this->getCommand('info -r "HEAD" | grep "Revision"');
	}

	/**
	 * Get the current branch
	 *
	 * @return string
	 */
	public function currentBranch()
	{
		return 'echo trunk';
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
		$repository = rtrim($repository, '/') . '/' . ltrim($branch, '/');

		return $this->getCommand('co %s %s %s', $this->getCredentials(), $repository, $destination);
	}

	/**
	 * Resets the repository
	 *
	 * @return string
	 */
	public function reset()
	{
		return $this->getCommand('status -q | grep -v \'^[~XI ]\' | awk \'{print $2;}\' | xargs %s revert', $this->binary);
	}

	/**
	 * Updates the repository
	 *
	 * @return string
	 */
	public function update()
	{
		return $this->getCommand('up %s', $this->getCredentials());
	}

	/**
	 * Return credential options
	 *
	 * @return string
	 */
	protected function getCredentials()
	{
		$options     = array('--non-interactive');
		$credentials = $this->app['rocketeer.rocketeer']->getCredentials();

		// Build command
		if ($user = array_get($credentials, 'username')) {
			$options[] = '--username=' . $user;
		}
		if ($pass = array_get($credentials, 'password')) {
			$options[] = '--password=' . $pass;
		}

		return implode(' ', $options);
	}

	/**
	 * Checkout the repository's submodules
	 *
	 * @return string
	 */
	public function submodules()
	{
		return '';
	}
}
