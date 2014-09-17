<?php
/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
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
