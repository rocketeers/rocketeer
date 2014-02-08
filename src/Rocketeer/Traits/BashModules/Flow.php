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
 * Handles the deployment flow (current/releases/shared)
 *
 * @author Maxime Fabre <ehtnam6@gmail.com>
 */
class Flow extends Scm
{
	/**
	 * Check if the remote server is setup
	 *
	 * @return boolean
	 */
	public function isSetup()
	{
		return $this->fileExists($this->rocketeer->getFolder('current'));
	}

	/**
	 * Check if the Task uses stages
	 *
	 * @return boolean
	 */
	public function usesStages()
	{
		$stages = $this->rocketeer->getStages();

		return $this->usesStages and !empty($stages);
	}

	/**
	 * Run actions in the current release's folder
	 *
	 * @param  string|array $tasks One or more tasks
	 *
	 * @return string
	 */
	public function runForCurrentRelease($tasks)
	{
		return $this->runInFolder($this->releasesManager->getCurrentReleasePath(), $tasks);
	}

	////////////////////////////////////////////////////////////////////
	//////////////////////////// SHARED FOLDERS ////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Sync the requested folders and files
	 *
	 * @return void
	 */
	protected function syncSharedFolders()
	{
		$shared = (array) $this->rocketeer->getOption('remote.shared');
		foreach ($shared as $file) {
			$this->share($file);
		}
	}

	/**
	 * Update the current symlink
	 *
	 * @param integer $release A release to mark as current
	 *
	 * @return string
	 */
	public function updateSymlink($release = null)
	{
		// If the release is specified, update to make it the current one
		if ($release) {
			$this->releasesManager->updateCurrentRelease($release);
		}

		// Get path to current/ folder and latest release
		$currentReleasePath = $this->releasesManager->getCurrentReleasePath();
		$currentFolder      = $this->rocketeer->getFolder('current');

		return $this->symlink($currentReleasePath, $currentFolder);
	}

	/**
	 * Share a file or folder between releases
	 *
	 * @param  string $file Path to the file in a release folder
	 *
	 * @return string
	 */
	public function share($file)
	{
		// Get path to current file and shared file
		$currentFile = $this->releasesManager->getCurrentReleasePath($file);
		$sharedFile  = preg_replace('#releases/[0-9]+/#', 'shared/', $currentFile);

		// If no instance of the shared file exists, use current one
		if (!$this->fileExists($sharedFile)) {
			$this->move($currentFile, $sharedFile);
		}

		$this->command->comment('Sharing file '.$currentFile);

		return $this->symlink($sharedFile, $currentFile);
	}
}
