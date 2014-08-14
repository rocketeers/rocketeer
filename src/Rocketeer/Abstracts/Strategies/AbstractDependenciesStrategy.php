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
		$manager  = $this->getManager();
		$manifest = $this->rocketeer->getFolder('current/'.$this->manifest);

		return $manager->getBinary() and $this->bash->fileExists($manifest);
	}

	/**
	 * Get an instance of the Binary
	 *
	 * @return \Rocketeer\Abstracts\AbstractBinary
	 */
	public function getManager()
	{
		return $this->binary($this->binary);
	}
}
