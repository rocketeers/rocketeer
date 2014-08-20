<?php
namespace Rocketeer\Abstracts\Strategies;

class AbstractCheckStrategy extends AbstractStrategy
{
	/**
	 * @type \Rocketeer\Strategies\Dependencies\ComposerStrategy
	 */
	protected $manager;

	/**
	 * The language of the strategy
	 *
	 * @type string
	 */
	protected $language;

	/**
	 * @return \Rocketeer\Strategies\Dependencies\ComposerStrategy
	 */
	public function getManager()
	{
		return $this->manager;
	}

	/**
	 * @return string
	 */
	public function getLanguage()
	{
		return $this->language;
	}

	//////////////////////////////////////////////////////////////////////
	/////////////////////////////// CHECKS ///////////////////////////////
	//////////////////////////////////////////////////////////////////////

	/**
	 * Check that the PM that'll install
	 * the app's dependencies is present
	 *
	 * @return boolean
	 */
	public function manager()
	{
		return $this->manager->isExecutable();
	}
}
