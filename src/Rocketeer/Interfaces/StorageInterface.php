<?php
namespace Rocketeer\Interfaces;

interface StorageInterface
{
	/**
	 * Get a value
	 *
	 * @param string|null $key
	 * @param string|null $fallback
	 *
	 * @return mixed
	 */
	public function get($key = null, $fallback = null);

	/**
	 * Set a value
	 *
	 * @param string|array $key
	 * @param mixed|null   $value
	 *
	 * @return void
	 */
	public function set($key, $value = null);

	/**
	 * Forget a value
	 *
	 * @param string $key
	 *
	 * @return void
	 */
	public function forget($key);

	/**
	 * Destroy the file
	 *
	 * @return boolean
	 */
	public function destroy();
}
