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

namespace Rocketeer\Services\Environment\Modules;

use Illuminate\Support\Str;

/**
 * Get paths on the current server.
 */
class ServerPathfinder extends AbstractPathfinderModule
{
    /**
     * @return string
     */
    public function getRootDirectory()
    {
        $rootDirectory = $this->connections->getCurrentConnectionKey()->root_directory;
        $rootDirectory = Str::finish($rootDirectory, '/');

        return $rootDirectory;
    }

    /**
     * Get the path to the root folder of the application.
     *
     * @return string
     */
    public function getHomeFolder()
    {
        $rootDirectory = $this->getRootDirectory();
        $appDirectory = $this->config->getContextually('remote.directories.app_directory') ?: $this->config->get('application_name');

        return $rootDirectory.$appDirectory;
    }

    /**
     * @param string[] ...$folder
     *
     * @return string
     */
    public function getCurrentFolder(...$folder)
    {
        return $this->getFolder($this->config->get('remote.directories.current'), ...$folder);
    }

    /**
     * @param string[] ...$folder
     *
     * @return string
     */
    public function getReleasesFolder(...$folder)
    {
        return $this->getFolder($this->config->get('remote.directories.releases'), ...$folder);
    }

    /**
     * Get the path to a folder, taking into account application name and stage.
     *
     * @param string|null ...$folder
     *
     * @return string
     */
    public function getFolder(...$folder)
    {
        $folder = implode('/', $folder);
        $folder = $this->modulable->replacePatterns($folder);

        $base = $this->connections->is('local') ? getcwd() : $this->getHomeFolder();
        $base = $base.'/';
        $stage = $this->connections->getCurrentConnectionKey()->stage;
        if ($folder && $stage) {
            $base .= $stage.'/';
        }

        // Replace base and cap it
        $folder = preg_replace('#^'.$base.'#', null, $folder);
        $folder = $base.$folder;

        return $folder;
    }

    /**
     * The methods this pathfinder provides.
     *
     * @return string[]
     */
    public function getProvided()
    {
        return [
            'getFolder',
            'getHomeFolder',
            'getCurrentFolder',
            'getReleasesFolder',
            'getRootDirectory',
        ];
    }
}
