<?php
namespace Rocketeer\Scm;

use Rocketeer\Traits\Scm;

/**
 * The Svn SCM
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

		return sprintf(
			$this->getCommand('co %s %s %s'),
			$this->getCredentials(),
			rtrim($repository, '/') . '/' . ltrim($branch, '/'),
			$destination
		);
	}

	/**
	 * Resets the repository
	 *
	 * @return string
	 */
	public function reset()
	{
		$cmd = 'status -q | grep -v \'^[~XI ]\' | awk \'{print $2;}\' | xargs %s revert';

		return $this->getCommand(sprintf($cmd, $this->binary));
	}

	/**
	 * Updates the repository
	 *
	 * @return string
	 */
	public function update()
	{
		return sprintf($this->getCommand('up %s'), $this->getCredentials());
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
}
