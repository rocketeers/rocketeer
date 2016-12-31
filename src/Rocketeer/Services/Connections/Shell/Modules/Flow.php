<?php

/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace Rocketeer\Services\Connections\Shell\Modules;

/**
 * Handles the deployment flow (current/releases/shared).
 */
class Flow extends AbstractBashModule
{
    /**
     * Whether the task needs to be run on each stage or globally.
     *
     * @var bool
     */
    public $usesStages = true;

    /**
     * Check if the remote server is setup.
     *
     * @return bool
     */
    public function isSetup()
    {
        return $this->modulable->fileExists(
            $this->paths->getCurrentFolder()
        );
    }

    /**
     * Setup the server if necessary.
     *
     * @return bool
     */
    public function setupIfNecessary()
    {
        // Check if local is ready for deployment
        return $this->modulable->on('local', function () {
            $primer = $this->modulable->executeTask('Primer');

            return $primer ?: $this->modulable->halt('Project is not ready for deploy. You were almost fired.');
        });

        if (!$this->isSetup()) {
            $this->explainer->error('Server is not ready, running Setup task');

            return $this->modulable->executeTask('Setup');
        }
    }

    /**
     * Check if the task uses stages.
     *
     * @return bool
     */
    public function usesStages()
    {
        $stages = $this->connections->getAvailableStages();

        return $this->usesStages && !empty($stages);
    }

    /**
     * Run actions in the current release's folder.
     *
     * @param string|array $tasks One or more tasks
     *
     * @return string
     */
    public function runForCurrentRelease($tasks)
    {
        return $this->modulable->runInFolder($this->releasesManager->getCurrentReleasePath(), $tasks);
    }

    /**
     * Run actions for the core of the application itself.
     *
     * @param string|array $tasks
     *
     * @return string
     */
    public function runForApplication($tasks)
    {
        $folder = $this->config->getContextually('remote.directories.subdirectory');
        $folder = $this->releasesManager->getCurrentReleasePath($folder);

        return $this->modulable->runInFolder($folder, $tasks);
    }

    /////////////////////////////////////////////////////////////////////
    /////////////////////////// PERMISSIONS /////////////////////////////
    /////////////////////////////////////////////////////////////////////

    /**
     * Set permissions for the folders used by the application.
     *
     * @return bool
     */
    public function setApplicationPermissions()
    {
        $files = (array) $this->config->getContextually('remote.permissions.files');
        foreach ($files as &$file) {
            $this->modulable->setPermissions($file);
        }

        return true;
    }

    ////////////////////////////////////////////////////////////////////
    //////////////////////////// SHARED FOLDERS ////////////////////////
    ////////////////////////////////////////////////////////////////////

    /**
     * Sync the requested folders and files.
     *
     * @return bool
     */
    public function syncSharedFolders()
    {
        $shared = (array) $this->config->getContextually('remote.shared');
        foreach ($shared as &$file) {
            $this->share($file);
        }

        return true;
    }

    /**
     * Update the current symlink.
     *
     * @param int|null $release A release to mark as current
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
        $currentFolder = $this->paths->getCurrentFolder();

        return $this->modulable->symlink($currentReleasePath, $currentFolder);
    }

    /**
     * Share a file or folder between releases.
     *
     * @param string $file Path to the file in a release folder
     *
     * @return string
     */
    public function share($file)
    {
        $mapping = $this->config->get('remote.directories');

        // Get path to current file and shared file
        $currentFile = $this->releasesManager->getCurrentReleasePath($file);
        $sharedFile = preg_replace('#'.$mapping['releases'].'/[0-9]+/#', $mapping['shared'].'/', $currentFile);

        // If no instance of the shared file exists, use current one
        if (!$this->modulable->fileExists($sharedFile)) {
            $this->modulable->move($currentFile, $sharedFile);
        }

        $this->explainer->line('Sharing file '.$currentFile);

        return $this->modulable->symlink($sharedFile, $currentFile);
    }

    /**
     * Copy a folder/file from the previous release.
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
        $folder = $this->releasesManager->getCurrentReleasePath($folder);

        return $this->modulable->copy($previous, $folder);
    }

    /**
     * @return string[]
     */
    public function getProvided()
    {
        return [
            'copyFromPreviousRelease',
            'isSetup',
            'runForApplication',
            'runForCurrentRelease',
            'setApplicationPermissions',
            'setupIfNecessary',
            'share',
            'syncSharedFolders',
            'updateSymlink',
            'usesStages',
        ];
    }
}
