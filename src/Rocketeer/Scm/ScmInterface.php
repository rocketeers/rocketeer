<?php
namespace Rocketeer\Scm;

/**
 * The interface for all SCMs
 */
interface ScmInterface
{
	/**
	 * Check if the SCM is available
	 *
	 * @return string
	 */
	public function check();

	/**
	 * Get the current state
	 *
	 * @return string
	 */
	public function currentState();

	/**
	 * Get the current branch
	 *
	 * @return string
	 */
	public function currentBranch();

	/**
	 * Clone a repository
	 *
	 * @param  string $destination
	 *
	 * @return string
	 */
	public function checkout($destination);

	/**
	 * Resets the repository
	 *
	 * @return string
	 */
	public function reset();

	/**
	 * Updates the repository
	 *
	 * @return string
	 */
	public function update();

	/**
	 * Checkout the repository's submodules
	 *
	 * @return string
	 */
	public function submodules();
}
