<?php
namespace Rocketeer\Services\Config\Files\Finders;

use Symfony\Component\Finder\SplFileInfo;

class ConsolidatedConfigurationFinder extends AbstractConfigurationFinder
{
    /**
     * @return SplFileInfo[]
     */
    public function getFiles()
    {
        $files = $this->getFinder($this->folder)->name('/^rocketeer/')->files();
        $files = iterator_to_array($files);
        if (!$files) {
            return [];
        }


        return [head($files)];
    }
}
