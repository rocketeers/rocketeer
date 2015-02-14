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
trait Flow
{
    /**
     * Whether the task needs to be run on each stage or globally
     *
     * @type boolean
     */
    public $usesStages = true;

    /**
     * Check if the remote server is setup
     *
     * @return boolean
     */
    public function isSetup()
    {
        return $this->fileExists($this->paths->getFolder('current'));
    }

    /**
     * Check if the task uses stages
     *
     * @return boolean
     */
    public function usesStages()
    {
        $stages = $this->connections->getAvailableStages();

        return $this->usesStages && !empty($stages);
    }

    /**
     * Run actions in the current release's folder
     *
     * @param string|array $tasks One or more tasks
     *
     * @return string
     */
    public function runForCurrentRelease($tasks)
    {
        return $this->runInFolder($this->releasesManager->getCurrentReleasePath(), $tasks);
    }

    /**
     * Run actions for the core of the application itself
     *
     * @param string|array $tasks
     *
     * @return string
     */
    public function runForApplication($tasks)
    {
        $folder = $this->rocketeer->getOption('remote.subdirectory');
        $folder = $this->releasesManager->getCurrentReleasePath($folder);

        return $this->runInFolder($folder, $tasks);
    }

    ////////////////////////////////////////////////////////////////////
    //////////////////////////// SHARED FOLDERS ////////////////////////
    ////////////////////////////////////////////////////////////////////

    /**
     * Sync the requested folders and files
     *
     * @return boolean
     */
    protected function syncSharedFolders()
    {
        $shared = (array) $this->rocketeer->getOption('remote.shared');
        foreach ($shared as &$file) {
            $this->share($file);
        }

        return true;
    }

    /**
     * Update the current symlink
     *
     * @param integer|null $release A release to mark as current
     *
     * @return string
     */
    public function updateSymlink($release = null)
    {
        // If the release is specified, update to make it the current one
        if ($release) {
            $this->releasesManager->setNextRelease($release);
        }

        // Get path to current/ folder and latest release
        $currentReleasePath = $this->releasesManager->getCurrentReleasePath();
        $currentFolder      = $this->paths->getFolder('current');

        return $this->symlink($currentReleasePath, $currentFolder);
    }

    /**
     * Share a file or folder between releases
     *
     * @param string $file Path to the file in a release folder
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

        $this->explainer->line('Sharing file '.$currentFile);

        return $this->symlink($sharedFile, $currentFile);
    }

    /**
     * Copy a folder/file from the previous release
     *
     * @param string $folder
     *
     * @return string
     */
    public function copyFromPreviousRelease($folder)
    {
        $previous = $this->releasesManager->getPreviousRelease();
        if (!$previous) {
            return;
        }

        $this->explainer->line('Copying file/folder '.$folder.' from previous release');
        $previous = $this->releasesManager->getPathToRelease($previous.'/'.$folder);
        $folder   = $this->releasesManager->getCurrentReleasePath($folder);

        return $this->copy($previous, $folder);
    }
}
