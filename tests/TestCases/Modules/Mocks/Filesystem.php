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

use League\Flysystem\Filesystem as LeagueFilesystem;
use Symfony\Component\Finder\Finder;

trait Filesystem
{
    /**
     * @param bool $withAdapter
     *
     * @return Filesystem
     */
    protected function bindFilesystemProphecy($withAdapter = false)
    {
        $adapter = $this->files->getAdapter();
        $prophecy = $this->bindProphecy(LeagueFilesystem::class);
        if ($withAdapter) {
            $prophecy->getAdapter()->willReturn($adapter);
        }

        return $prophecy;
    }

    /**
     * Recreate the local filesystem onto VFS.
     */
    protected function recreateVirtualServer()
    {
        $this->files->createDir($this->server.'/shared');
        $this->files->createDir($this->server.'/releases/10000000000000');
        $this->files->createDir($this->server.'/releases/15000000000000');
        $this->files->createDir($this->server.'/releases/20000000000000');
        $this->files->createDir($this->server.'/current');

        $this->files->put($this->server.'/state.json', json_encode([
            '10000000000000' => true,
            '15000000000000' => false,
            '20000000000000' => true,
        ]));

        $this->files->put($this->server.'/deployments.json', json_encode([
            'foo' => 'bar',
            'directory_separator' => '\\/',
            'is_setup' => true,
            'webuser' => ['username' => 'www-data', 'group' => 'www-data'],
            'line_endings' => "\n",
        ]));
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

        $this->files->createDir($folder);
        $files = (new Finder())->in($folder)->files();
        foreach ($files as $file) {
            $contents = file_get_contents($file->getPathname());
            $this->files->write($file->getPathname(), $contents);
        }
    }
}
