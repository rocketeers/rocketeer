<?php
/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Rocketeer\Strategies\Deploy;

use Rocketeer\Abstracts\Strategies\AbstractStrategy;
use Rocketeer\Bash;
use Rocketeer\Interfaces\Strategies\DeployStrategyInterface;

class SyncStrategy extends AbstractStrategy implements DeployStrategyInterface
{
	/**
	 * @type string
	 */
	protected $description = 'Uses rsync to create or update a release from the local files';

	/**
	 * Deploy a new clean copy of the application
	 *
	 * @param string|null $destination
	 *
	 * @return boolean
	 */
	public function deploy($destination = null)
	{
		if (!$destination) {
			$destination = $this->releasesManager->getCurrentReleasePath();
		}

		// Create receiveing folder
		$this->createFolder($destination);

		return $this->rsyncTo($destination);
	}

	/**
	 * Update the latest version of the application
	 *
	 * @param boolean $reset
	 *
	 * @return boolean
	 */
	public function update($reset = true)
	{
		$release = $this->releasesManager->getCurrentReleasePath();

		return $this->rsyncTo($release);
	}

	/**
	 * Rsyncs the local folder to a remote one
	 *
	 * @param string $destination
	 *
	 * @return boolean
	 */
	protected function rsyncTo($destination)
	{
		// Build host handle
		$credentials = $this->connections->getServerCredentials();
		$handle      = array_get($credentials, 'host');
		if ($user = array_get($credentials, 'username')) {
			$handle = $user.'@'.$handle;
		}

		// Create options
		$options  = '--verbose --recursive --rsh="ssh"';
		$excludes = ['.git', 'vendor'];
		foreach ($excludes as $exclude) {
			$options .= ' --exclude="'.$exclude.'"';
		}

		// Create binary and command
		$rsync = $this->binary('rsync');
		$rsync = $rsync->getCommand(null, ['./', $handle.':'.$destination], $options);

		return $this->bash->onLocal(function (Bash $bash) use ($rsync) {
			return $bash->run($rsync);
		});
	}
}
