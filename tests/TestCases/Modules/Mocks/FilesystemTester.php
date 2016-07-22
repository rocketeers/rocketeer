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

namespace Rocketeer\TestCases\Modules\Mocks;

use Rocketeer\Services\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

trait FilesystemTester
{
    /**
     * @param bool $withAdapter
     *
     * @return FilesystemTester
     */
    protected function bindFilesystemProphecy($withAdapter = false)
    {
        $adapter = $this->files->getAdapter();
        $prophecy = $this->bindProphecy(Filesystem::class);
        if ($withAdapter) {
            $prophecy->getAdapter()->willReturn($adapter);
        }

        return $prophecy;
    }

    /**
     * Replicates the configuration onto the VFS.
     */
    protected function replicateConfiguration()
    {
        $folder = $this->configurationLoader->getFolders()[0];

        $this->replicateFolder($folder);
        $this->replicateFolder($folder.'/../stubs');

        $this->configurationLoader->setFolders([$folder]);
        $this->configurationLoader->getCache()->flush();

        return $folder;
    }

    /**
     * @param string $folder
     */
    protected function replicateFolder($folder)
    {
        $folder = realpath($folder);
        $files = (new Finder())->in($folder);

        foreach ($files as $file) {
            $pathname = $file->getPathname();

            if ($file->isDir()) {
                $this->files->createDir($pathname);
            } else {
                $contents = file_get_contents($pathname);
                $this->files->write($pathname, $contents);
            }
        }
    }
}
