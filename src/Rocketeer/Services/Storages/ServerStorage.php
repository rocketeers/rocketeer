<?php
/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Rocketeer\Services\Storages;

use Illuminate\Support\Arr;
use Rocketeer\Traits\HasLocator;

/**
 * Provides and persists informations on the server
 *
 * @author Maxime Fabre <ehtnam6@gmail.com>
 */
class ServerStorage
{
	use HasLocator;

	/**
	 * Get a value on the server
	 *
	 * @param string      $file
	 * @param string|null $key
	 *
	 * @return mixed
	 */
	public function get($file, $key = null)
	{
		$contents = $this->getContents($file);

		return Arr::get($contents, $key);
	}

	/**
	 * Set a value on the server
	 *
	 * @param string     $file
	 * @param string     $key
	 * @param mixed|null $value
	 */
	public function set($file, $key, $value = null)
	{
		if (is_null($value)) {
			return $this->saveContents($file, $key);
		}

		$contents = $this->getContents($file);
		$contents = Arr::set($contents, $key, $value);
		$this->saveContents($file, $contents);
	}

	//////////////////////////////////////////////////////////////////////
	////////////////////////////// HELPERS ///////////////////////////////
	//////////////////////////////////////////////////////////////////////

	/**
	 * Get the full path to a file
	 *
	 * @param string $file
	 *
	 * @return string
	 */
	protected function getFilepath($file)
	{
		return $this->rocketeer->getFolder($file.'.json');
	}

	/**
	 * Get the contents of a file
	 *
	 * @param string $file
	 *
	 * @return array
	 */
	protected function getContents($file)
	{
		$file = $this->getFilepath($file);
		$file = $this->bash->getFile($file) ?: '{}';
		$file = (array) json_decode($file, true);

		return $file;
	}

	/**
	 * Save the contents of a file
	 *
	 * @param string $file
	 * @param array  $contents
	 */
	protected function saveContents($file, $contents)
	{
		$file = $this->getFilepath($file);
		$this->bash->putFile($file, json_encode($contents));
	}
}
