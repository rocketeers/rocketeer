<?php
/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Rocketeer\Traits\BashModules;

/**
 * Files and folders handling
 *
 * @author Maxime Fabre <ehtnam6@gmail.com>
 */
class Filesystem extends Core
{
	////////////////////////////////////////////////////////////////////
	/////////////////////////////// COMMON /////////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Symlinks two folders
	 *
	 * @param  string $folder   The folder in shared/
	 * @param  string $symlink  The folder that will symlink to it
	 *
	 * @return string
	 */
	public function symlink($folder, $symlink)
	{
		if (!$this->fileExists($folder)) {
			if (!$this->fileExists($symlink)) {
				return false;
			}

			$this->move($symlink, $folder);
		}

		// Remove existing symlink
		$this->removeFolder($symlink);

		return $this->run(sprintf('ln -s %s %s', $folder, $symlink));
	}

	/**
	 * Move a file
	 *
	 * @param  string $origin
	 * @param  string $destination
	 *
	 * @return string
	 */
	public function move($origin, $destination)
	{
		return $this->fromTo('mv', $origin, $destination);
	}

	/**
	 * Copy a file
	 *
	 * @param string $origin
	 * @param string $destination
	 *
	 * @return string
	 */
	public function copy($origin, $destination)
	{
		return $this->fromTo('cp', $origin, $destination);
	}

	/**
	 * Get the contents of a directory
	 *
	 * @param  string $directory
	 *
	 * @return array
	 */
	public function listContents($directory)
	{
		return $this->run('ls '.$directory, true, true);
	}

	/**
	 * Check if a file exists
	 *
	 * @param  string $file Path to the file
	 *
	 * @return boolean
	 */
	public function fileExists($file)
	{
		$exists = $this->runRaw('[ -e ' .$file. ' ] && echo "true"');

		return trim($exists) == 'true';
	}

	/**
	 * Execute permissions actions on a file with the provided callback
	 *
	 * @param string $folder
	 *
	 * @return  string
	 */
	public function setPermissions($folder)
	{
		// Get path to folder
		$folder = $this->releasesManager->getCurrentReleasePath($folder);
		$this->command->comment('Setting permissions for '.$folder);

		// Get permissions options
		$callback = $this->rocketeer->getOption('remote.permissions.callback');
		$commands = (array) $callback($this, $folder);

		// Cancel if setting of permissions is not configured
		if (empty($commands)) {
			return true;
		}

		return $this->runForCurrentRelease($commands);
	}

	////////////////////////////////////////////////////////////////////
	//////////////////////////////// FILES /////////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Get the contents of a file
	 *
	 * @param string $file
	 *
	 * @return string
	 */
	public function getFile($file)
	{
		return $this->remote->getString($file);
	}

	/**
	 * Write to a file
	 *
	 * @param string $file
	 * @param string $contents
	 *
	 * @return void
	 */
	public function putFile($file, $contents)
	{
		$this->remote->putString($file, $contents);
	}

	////////////////////////////////////////////////////////////////////
	/////////////////////////////// FOLDERS ////////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Create a folder in the application's folder
	 *
	 * @param  string  $folder       The folder to create
	 * @param  boolean $recursive
	 *
	 * @return string The task
	 */
	public function createFolder($folder = null, $recursive = false)
	{
		$recursive = $recursive ? '-p ' : null;

		return $this->run('mkdir '.$recursive.$this->rocketeer->getFolder($folder));
	}

	/**
	 * Remove a folder in the application's folder
	 *
	 * @param  string $folder       The folder to remove
	 *
	 * @return string The task
	 */
	public function removeFolder($folder = null)
	{
		return $this->run('rm -rf '.$this->rocketeer->getFolder($folder));
	}

	////////////////////////////////////////////////////////////////////
	/////////////////////////////// HELPERS ////////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Execute a "from/to" style command
	 *
	 * @param string $command
	 * @param string $from
	 * @param string $to
	 *
	 * @return string
	 */
	protected function fromTo($command, $from, $to)
	{
		$folder = dirname($to);
		if (!$this->fileExists($folder)) {
			$this->createFolder($folder, true);
		}

		return $this->run(sprintf('%s %s %s', $command, $from, $to));
	}
}
