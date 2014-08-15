<?php
/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Rocketeer\Binaries;

use Rocketeer\Abstracts\AbstractBinary;

class Composer extends AbstractBinary
{
	/**
	 * Get an array of default paths to look for
	 *
	 * @return array
	 */
	protected function getKnownPaths()
	{
		return array(
			'composer',
			$this->releasesManager->getCurrentReleasePath().'/composer.phar'
		);
	}

	/**
	 * Change Composer's binary
	 *
	 * @param string $binary
	 */
	public function setBinary($binary)
	{
		parent::setBinary($binary);

		// Prepend PHP command if executing from archive
		if (strpos($this->getBinary(), 'composer.phar') !== false) {
			$php = new Php($this->app);
			$this->setParent($php);
		}
	}
}
