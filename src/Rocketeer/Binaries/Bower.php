<?php
namespace Rocketeer\Binaries;

use Rocketeer\Abstracts\AbstractBinary;

class Bower extends AbstractBinary
{
	/**
	 * Get an array of default paths to look for
	 *
	 * @return string[]
	 */
	protected function getKnownPaths()
	{
		return array(
			'bower',
			$this->releasesManager->getCurrentReleasePath().'/node_modules/.bin/bower'
		);
	}
}
