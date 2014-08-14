<?php
namespace Rocketeer\Abstracts\Strategies;

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
		return $this->getManager()->getBinary() and $this->hasManifest();
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

		$local = 	$this->app['path.base'].DS.$this->manifest;
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
