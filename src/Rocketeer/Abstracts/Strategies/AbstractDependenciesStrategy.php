<?php
/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Rocketeer\Abstracts\Strategies;

/**
 * Abstract class for Dependencies strategies
 *
 * @author Maxime Fabre <ehtnam6@gmail.com>
 */
abstract class AbstractDependenciesStrategy extends AbstractStrategy
{
	/**
	 * The name of the manifest file to look for
	 *
	 * @type string
	 */
	protected $manifest;

	/**
	 * The name of the binary
	 *
	 * @type string
	 */
	protected $binary;

	/**
	 * Whether this particular strategy is runnable or not
	 *
	 * @return boolean
	 */
	public function isExecutable()
	{
		return $this->getManager()->getBinary() && $this->hasManifest();
	}

	//////////////////////////////////////////////////////////////////////
	////////////////////////////// COMMANDS //////////////////////////////
	//////////////////////////////////////////////////////////////////////

	/**
	 * Install the dependencies
	 *
	 * @return bool
	 */
	public function install()
	{
		return $this->getManager()->runForCurrentRelease('install');
	}

	/**
	 * Update the dependencies
	 *
	 * @return boolean
	 */
	public function update()
	{
		return $this->getManager()->runForCurrentRelease('update');
	}

	//////////////////////////////////////////////////////////////////////
	////////////////////////////// HELPERS ///////////////////////////////
	//////////////////////////////////////////////////////////////////////

	/**
	 * Check if the manifest file exists, locally or on server
	 *
	 * @return bool
	 */
	protected function hasManifest()
	{
		$server = $this->rocketeer->getFolder('current/'.$this->manifest);
		$server = $this->bash->fileExists($server);

		$local = $this->app['path.base'].DS.$this->manifest;
		$local = $this->files->exists($local);

		return $local || $server;
	}

	/**
	 * Get an instance of the Binary
	 *
	 * @return \Rocketeer\Abstracts\AbstractBinary
	 */
	protected function getManager()
	{
		return $this->binary($this->binary);
	}
}
