<?php
namespace Rocketeer\Abstracts\Strategies;

class AbstractCheckStrategy extends AbstractStrategy
{
	/**
	 * @type \Rocketeer\Abstracts\AbstractPackageManager
	 */
	protected $manager;

	/**
	 * The language of the strategy
	 *
	 * @type string
	 */
	protected $language;

	/**
	 * @return \Rocketeer\Abstracts\AbstractPackageManager
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

	/**
	 * Check that the language used by the
	 * application is at the required version
	 *
	 * @return boolean
	 */
	public function language()
	{
		$required = null;

		$required = null;

		// Get the minimum version of the application
		if ($manifest = $this->manager->getManifestContents()) {
			$required = $this->getLanguageConstraint($manifest);
		}

		// Cancel if no version constraint
		if (!$required) {
			return true;
		}

		$version = $this->getCurrentVersion();

		return version_compare($version, $required, '>=');
	}

	//////////////////////////////////////////////////////////////////////
	////////////////////////////// LANGUAGE //////////////////////////////
	//////////////////////////////////////////////////////////////////////

	/**
	 * Get the version constraint which should be checked against
	 *
	 * @param string $manifest
	 *
	 * @return string
	 */
	abstract protected function getLanguageConstraint($manifest);

	/**
	 * Get the current version in use
	 *
	 * @return string
	 */
	abstract protected function getCurrentVersion();
}
