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
        $appDirectory = $this->config->getContextually('remote.app_directory') ?: $this->config->get('application_name');

        return $rootDirectory.$appDirectory;
    }

    /**
     * Get the path to a folder, taking into account application name and stage.
     *
     * @param string|null $folder
     *
     * @return string
     */
    public function getFolder($folder = null)
    {
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
            'getRootDirectory',
        ];
    }
}
