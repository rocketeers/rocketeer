<?php
namespace Rocketeer\Scm;

/**
 * The interface for all SCMs
 */
interface Scm
{

	/**
	 * Get the current state of the repository
	 *
	 * @return string
	 */
	public function current();

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

}