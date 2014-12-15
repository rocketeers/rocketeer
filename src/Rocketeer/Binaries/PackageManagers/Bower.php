<?php
/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Rocketeer\Binaries\PackageManagers;

use Rocketeer\Abstracts\AbstractPackageManager;

class Bower extends AbstractPackageManager
{
	/**
	 * The name of the manifest file to look for
	 *
	 * @type string
	 */
	protected $manifest = 'bower.json';

	/**
	 * Get an array of default paths to look for
	 *
	 * @return string[]
	 */
	protected function getKnownPaths()
	{
		return array(
			'bower',
			$this->releasesManager->getCurrentReleasePath().'/node_modules/.bin/bower',
		);
	}

	/**
	 * Get where dependencies are installed
	 *
	 * @return string
	 */
	public function getDependenciesFolder()
	{
		// Look for a configuration file
		$paths = array_filter(array(
			$this->paths->getApplicationPath().'.bowerrc',
			$this->paths->getUserHomeFolder().'/.bowerrc',
		), [$this->files, 'exists']);

		$file = head($paths);
		if ($file) {
			$file = file_get_contents($file);
			$file = json_decode($file, true);
		}

		return array_get($file, 'directory', 'bower_components');
	}
}
