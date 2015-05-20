<?php
namespace Rocketeer\Services\Environment\Pathfinders;

use Illuminate\Support\Str;

class ServerPathfinder extends AbstractPathfinder
{
    /**
     * Get the path to the root folder of the application.
     *
     * @return string
     */
    public function getHomeFolder()
    {
        $rootDirectory = $this->connections->getCurrentConnection()->root_directory;
        $rootDirectory = Str::finish($rootDirectory, '/');
        $appDirectory  = $this->rocketeer->getOption('remote.app_directory') ?: $this->rocketeer->getApplicationName();

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

        $base  = $this->getHomeFolder().'/';
        $stage = $this->connections->getCurrentConnection()->stage;
        if ($folder && $stage) {
            $base .= $stage.'/';
        }
        $folder = str_replace($base, null, $folder);

        return $base.$folder;
    }

    /**
     * The methods this pathfinder provides
     *
     * @return string[]
     */
    public function provides()
    {
        return [
            'getHomeFolder',
            'getFolder',
        ];
    }
}
