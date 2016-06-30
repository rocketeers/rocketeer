<?php

/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Rocketeer\Services\Environment\Pathfinders;

use Illuminate\Support\Str;

class ServerPathfinder extends AbstractPathfinder
{
    /**
     * @return string
     */
    public function getRootDirectory()
    {
        $rootDirectory = $this->connections->getCurrentConnection()->root_directory;
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
        $appDirectory = $this->config->getContextually('remote.app_directory') ?: $this->rocketeer->getApplicationName();

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
        $folder = $this->replacePatterns($folder);

        $base = $this->getHomeFolder().'/';
        $stage = $this->connections->getCurrentConnection()->stage;
        if ($folder && $stage) {
            $base .= $stage.'/';
        }
        $folder = str_replace($base, null, $folder);

        return $base.$folder;
    }

    /**
     * The methods this pathfinder provides.
     *
     * @return string[]
     */
    public function provides()
    {
        return [
            'getRootDirectory',
            'getHomeFolder',
            'getFolder',
        ];
    }
}
