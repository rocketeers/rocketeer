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
 * Files and folders handling.
 *
 * @author Maxime Fabre <ehtnam6@gmail.com>
 */
trait Filesystem
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
        if ($this->rocketeer->getOption('remote.symlink') === 'relative') {
            $folder = str_ireplace($this->paths->getFolder(''), '', $folder);
        }

        switch ($this->environment->getOperatingSystem()) {
            case 'Linux':
                return $this->symlinkSwap($folder, $symlink);

            default:
                if ($this->fileExists($symlink)) {
                    $this->removeFolder($symlink);
                }

                return $this->run([
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

        return $this->run([
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
        return $this->run('ls '.$directory, true, true);
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
        return $this->checkStatement('-e "'.$file.'"');
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
     * Get the contents of a file.
     *
     * @param string $file
     *
     * @return string
     */
    public function getFile($file)
    {
        return $this->getConnection()->getString($file);
    }

    /**
     * Write to a file.
     *
     * @param string $file
     * @param string $contents
     */
    public function putFile($file, $contents)
    {
        $this->getConnection()->putString($file, $contents);
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

        $this->getConnection()->put($file, $destination);
    }

    ////////////////////////////////////////////////////////////////////
    /////////////////////////////// FOLDERS ////////////////////////////
    ////////////////////////////////////////////////////////////////////

    /**
     * Create a folder in the application's folder.
     *
     * @param string|null $folder    The folder to create
     * @param bool        $recursive
     *
     * @return string The task
     */
    public function createFolder($folder = null, $recursive = false)
    {
        $recursive = $recursive ? '-p ' : null;

        return $this->run('mkdir '.$recursive.$this->paths->getFolder($folder));
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

        return $this->run('rm -rf '.$folders);
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
        $condition = $this->runRaw($condition);

        return trim($condition) === 'true';
    }

    /**
     * Execute a "from/to" style command.
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
