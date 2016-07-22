<?php
namespace Rocketeer\Strategies\Deploy;

use Symfony\Component\Finder\Finder;

class LocalUploadStrategy extends LocalCloneStrategy
{
    /**
     * @var string
     */
    protected $description = 'Uses FTP to upload a release from a locally cloned repository';

    /**
     * Deploy a new clean copy of the application.
     *
     * @param string|null $destination
     *
     * @return bool
     */
    public function deploy($destination = null)
    {
        if (!$destination) {
            $destination = $this->releasesManager->getCurrentReleasePath();
        }

        // Create receiveing folder
        $this->createFolder($destination);

        // Clone application locally
        $tmpDirectoryPath = $this->getCloneDirectory();
        $this->createCloneDirectory($tmpDirectoryPath);
        $this->cloneLocally($tmpDirectoryPath);

        return $this->uploadTo($destination, $tmpDirectoryPath);
    }

    /**
     * @param string $destination
     * @param string $tmpDirectoryPath
     */
    protected function uploadTo($destination, $tmpDirectoryPath)
    {
        $this->explainer->comment('Uploading files to server');

        $files = (new Finder())->in($tmpDirectoryPath)->exclude(['.git']);
        $this->command->progressStart(iterator_count($files));
        foreach ($files as $file) {
            $path = $file->getPathname();
            $path = str_replace($tmpDirectoryPath, null, $path);
            $path = $destination.DS.$path;

            if ($file->isDir()) {
                $this->createFolder($path);
            } else {
                $this->put($path, $file->getContents());
            }

            $this->command->progressAdvance();
        }

        $this->command->progressAdvance();
    }
}
