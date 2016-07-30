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
 * Files and folders handling.
 */
class Filesystem extends AbstractBashModule
{
    ////////////////////////////////////////////////////////////////////
    /////////////////////////////// COMMON /////////////////////////////
    ////////////////////////////////////////////////////////////////////

    /**
     * Check if a file or folder is a symlink.
     *
     * @param string $folder
     *
     * @return bool
     */
    public function isSymlink($folder)
    {
        return $this->checkStatement('-L "'.$folder.'"');
    }

    /**
     * Symlinks two folders.
     *
     * @param string $folder  The folder in shared/
     * @param string $symlink The folder that will symlink to it
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

        // Switch to relative if required
        if ($this->config->getContextually('remote.symlink') === 'relative') {
            $folder = $this->paths->computeRelativePathBetween($symlink, $folder);
        }

        switch ($this->environment->getOperatingSystem()) {
            case 'Linux':
                return $this->symlinkSwap($folder, $symlink);
            default:
                if ($this->fileExists($symlink)) {
                    $this->removeFolder($symlink);
                }

                return $this->modulable->run([
                    sprintf('ln -s %s %s', $folder, $symlink),
                ]);
        }
    }

    /**
     * Swap a symlink if possible.
     *
     * @param string $folder
     * @param string $symlink
     *
     * @return string
     */
    protected function symlinkSwap($folder, $symlink)
    {
        if ($this->fileExists($symlink) && !$this->isSymlink($symlink)) {
            $this->removeFolder($symlink);
        }

        // Define name of temporary link
        $temporary = $symlink.'-temp';

        return $this->modulable->run([
            sprintf('ln -s %s %s', $folder, $temporary),
            sprintf('mv -Tf %s %s', $temporary, $symlink),
        ]);
    }

    /**
     * Move a file.
     *
     * @param string $origin
     * @param string $destination
     *
     * @return string|null
     */
    public function move($origin, $destination)
    {
        if (!$this->fileExists($origin)) {
            return;
        }

        return $this->fromTo('mv', $origin, $destination);
    }

    /**
     * Copy a file.
     *
     * @param string $origin
     * @param string $destination
     *
     * @return string
     */
    public function copy($origin, $destination)
    {
        return $this->fromTo('cp -a', $origin, $destination);
    }

    /**
     * Get the contents of a directory.
     *
     * @param string $directory
     *
     * @return array
     */
    public function listContents($directory)
    {
        $files = $this->modulable->getConnection()->listContents($directory);
        $files = array_pluck($files, 'path');
        $files = array_map('basename', $files);

        return $files;
    }

    /**
     * Check if a file exists.
     *
     * @param string $file Path to the file
     *
     * @return bool
     */
    public function fileExists($file)
    {
        return $this->modulable->getConnection()->has($file);
    }

    /**
     * Execute permissions actions on a file with the provided callback.
     *
     * @param string $folder
     *
     * @return string
     */
    public function setPermissions($folder)
    {
        // Get path to folder
        $folder = $this->releasesManager->getCurrentReleasePath($folder);
        $this->explainer->line('Setting permissions for '.$folder);

        // Get permissions options
        $callback = $this->config->getContextually('remote.permissions.callback');
        $commands = (array) $callback($folder, $this);

        // Cancel if setting of permissions is not configured
        if (empty($commands)) {
            return true;
        }

        return $this->modulable->runForCurrentRelease($commands);
    }

    ////////////////////////////////////////////////////////////////////
    //////////////////////////////// FILES /////////////////////////////
    ////////////////////////////////////////////////////////////////////

    /**
     * Get the contents of a file.
     *
     * @param string $file
     *
     * @return string
     */
    public function read($file)
    {
        return $this->modulable->getConnection()->read($file);
    }

    /**
     * Write to a file.
     *
     * @param string $file
     * @param string $contents
     */
    public function put($file, $contents)
    {
        $this->modulable->getConnection()->put($file, $contents);
    }

    /**
     * Upload a local file to remote.
     *
     * @param string      $file
     * @param string|null $destination
     */
    public function upload($file, $destination = null)
    {
        if (!file_exists($file)) {
            return;
        }

        // Get contents and destination
        $destination = $destination ?: basename($file);

        $this->put($destination, file_get_contents($file));
    }

    /**
     * Tail the contents of a file.
     *
     * @param string $file
     * @param bool   $continuous
     *
     * @return string|null
     */
    public function tail($file, $continuous = true)
    {
        $continuous = $continuous ? ' -f' : null;
        $command = sprintf('tail %s %s', $file, $continuous);

        return $this->modulable->run($command);
    }

    ////////////////////////////////////////////////////////////////////
    /////////////////////////////// FOLDERS ////////////////////////////
    ////////////////////////////////////////////////////////////////////

    /**
     * Create a folder in the application's folder.
     *
     * @param string|null $folder The folder to create
     *
     * @return string The task
     */
    public function createFolder($folder = null)
    {
        $folder = $this->paths->getFolder($folder);
        $this->modulable->toHistory('mkdir '.$folder);

        return $this->modulable->getConnection()->createDir($folder);
    }

    /**
     * Remove a folder in the application's folder.
     *
     * @param array|string|null $folders The folder to remove
     *
     * @return string The task
     */
    public function removeFolder($folders = null)
    {
        $folders = (array) $folders;
        $folders = array_map([$this->paths, 'getFolder'], $folders);
        $folders = implode(' ', $folders);

        return $this->modulable->run('rm -rf '.$folders);
    }

    ////////////////////////////////////////////////////////////////////
    /////////////////////////////// HELPERS ////////////////////////////
    ////////////////////////////////////////////////////////////////////

    /**
     * Check a condition via Bash.
     *
     * @param string $condition
     *
     * @return bool
     */
    protected function checkStatement($condition)
    {
        $condition = '[ '.$condition.' ] && echo "true"';
        $condition = $this->modulable->runRaw($condition);

        return trim($condition) === 'true';
    }

    /**
     * Execute a "from/to" style command.
     *
     * @param string $command
     * @param string $from
     * @param string $destination
     *
     * @return string
     */
    protected function fromTo($command, $from, $destination)
    {
        $folder = dirname($destination);
        if (!$this->fileExists($folder)) {
            $this->createFolder($folder);
        }

        return $this->modulable->run(sprintf('%s %s %s', $command, $from, $destination));
    }

    /**
     * @return string[]
     */
    public function getProvided()
    {
        return [
            'copy',
            'createFolder',
            'fileExists',
            'isSymlink',
            'listContents',
            'move',
            'put',
            'read',
            'removeFolder',
            'setPermissions',
            'symlink',
            'tail',
            'upload',
        ];
    }
}
