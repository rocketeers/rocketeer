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
 *
 * @author Maxime Fabre <ehtnam6@gmail.com>
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
            $folder = $this->getRelativePath($symlink, $folder);
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
        $commands = (array) $callback($this, $folder);

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
     * @param string|null $folder    The folder to create
     * @param bool        $recursive
     *
     * @return string The task
     */
    public function createFolder($folder = null, $recursive = false)
    {
        $recursive = $recursive ? '-p ' : null;

        return $this->modulable->run('mkdir '.$recursive.$this->paths->getFolder($folder));
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
            $this->createFolder($folder, true);
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

    /**
     * Get a relative path from one file or directory to another.
     *
     * If $from is a path to a file (i.e. does not end with a "/"), the
     * returned path will be relative to its parent directory.
     *
     * @param string $from
     * @param string $to
     *
     * @return string
     */
    protected function getRelativePath($from, $to)
    {
        $from = $this->explodePath($from);
        $to   = $this->explodePath($to);

        $result = [];
        $i      = 0;

        // Skip the common path prefix
        while ($i < count($from) && $i < count($to) && $from[$i] === $to[$i]) {
            $i++;
        }

        // Add ".." for each directory left in $from
        $from_length = count($from) - 1; // Path length without the filename
        if ($i > 0 && $i < $from_length) {
            $result = array_fill(0, $from_length - $i, '..');
        }

        // Add the remaining $to path
        $result = array_merge($result, array_slice($to, $i));

        return implode('/', $result);
    }

    /**
     * Explode the given path into components, resolving any
     * ".." components and ignoring "." and double separators.
     *
     * If the path starts at root directory, the first component
     * will be empty.
     *
     * @param string $path
     * @param string $separator Path separator to use, defaults to /.
     *
     * @return array
     */
    protected function explodePath($path, $separator = '/')
    {
        $result = [];

        if (strpos($path, $separator) === 0) {
            // Add empty component if the path starts at root directory
            $result[] = '';
        }

        foreach (explode($separator, $path) as $component) {
            switch ($component) {
                case '..':
                    // ".." removes the preceding component
                    if (empty($result) || $result[count($result) - 1] === '..') {
                        // Unless the path contains only ".." so far (then keep the "..")
                        $result[] = '..';
                        break;
                    }
                    if (count($result) === 1 && $result[0] === '') {
                        // Or the path is already at root (then just ignore it)
                        break;
                    }
                    array_pop($result);
                    break;
                case '.':
                case '':
                    // Ignore double separators and "."
                    break;
                default:
                    $result[] = $component;
            }
        }

        return $result;
    }
}
