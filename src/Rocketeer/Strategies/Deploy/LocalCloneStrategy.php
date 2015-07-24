<?php

/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Rocketeer\Strategies\Deploy;

use Carbon\Carbon;
use Exception;

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
     * Create clone directory if not exists.
     *
     * @param string $temporaryFolderPath
     *
     * @throws Exception
     */
    protected function createCloneDirectory($temporaryFolderPath)
    {
        if (!$this->files->isDirectory($temporaryFolderPath)) {
            if (!$this->files->makeDirectory($temporaryFolderPath, 0777, true)) {
                throw new Exception('['.__METHOD__."] Can't create clone directory : ".$temporaryFolderPath);
            }
        }
    }

    /**
     * Clone repository locally.
     *
     * @param string $directory
     *
     * @return bool
     */
    protected function cloneLocally($directory)
    {
        return $this->bash->onLocal(function () use ($directory) {
            return $this->scm->run('checkout', $directory);
        });
    }

    /**
     * Get the directory to clone in
     *
     * @return string
     */
    protected function getCloneDirectory()
    {
        $storagePath = $this->paths->getStoragePath();
        $timestamp   = Carbon::now()->timestamp;

        return $storagePath.'/checkout/tmp/'.$timestamp.'/';
    }
}
