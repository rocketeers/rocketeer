<?php

namespace Rocketeer\Strategies\Deploy;
use Rocketeer\Strategies\Deploy\SyncStrategy;
use Rocketeer\Bash;

class LocalCloneStrategy extends SyncStrategy
{
    /**
     * @type string
     */
    protected $description = 'Uses rsync to create or update a release from local temporary cloned repository';
    
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
        
        $tmpDirectoryPath = $this->getCloneDirectory();
        $this->createCloneDirectory($tmpDirectoryPath);
        
        $this->cloneLocally($tmpDirectoryPath);
        
        return $this->rsyncTo($destination, $tmpDirectoryPath);
    }
    
    /**
     * Create clone directory if not exists
     */
    protected function createCloneDirectory($tmpFolderPath)
    {
        if (!$this->files->isDirectory($tmpFolderPath)) {
            if(!$this->files->makeDirectory($tmpFolderPath, 0777, true)) {
                throw new \Exception("[" . __METHOD__ . "] Can't create clone directory : " . $tmpFolderPath);
            }
        }
    }
    
    /**
     * Clone repository locally
     * 
     * @param string $directory
     */
    protected function cloneLocally($directory)
    {
        return $this->bash->onLocal(function (Bash $bash) use ($directory) {
            return $this->scm->run('checkout', $directory);
        });
    }
    
    protected function getCloneDirectory()
    {
        $storagePath = $this->paths->getStoragePath();
        
        return $storagePath . '/checkout/tmp/' . time() . "/";
    }
}
