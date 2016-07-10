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

namespace Rocketeer\Services\Filesystem;

use League\Flysystem\Filesystem;
use League\Flysystem\MountManager;
use Rocketeer\Services\Connections\ConnectionsHandler;
use Rocketeer\Traits\ContainerAwareTrait;

class FilesystemsMounter
{
    use ContainerAwareTrait;

    /**
     * @var ConnectionKeyAdapter[]
     */
    protected $filesystems;

    /**
     * @return MountManager
     */
    public function getMountManager()
    {
        $this->filesystems = ['local' => $this->files];

        if ($this->container->has(ConnectionsHandler::class) && $this->connections->hasCurrentConnection()) {
            $this->gatherRemoteFilesystems();
        }

        return new MountManager($this->filesystems);
    }

    /**
     * Gather the remote filesystems to mount.
     */
    protected function gatherRemoteFilesystems()
    {
        $connections = $this->connections->getAvailableConnections();
        $current = $this->connections->getCurrentConnectionKey()->toHandle();

        foreach ($connections as $name => $servers) {
            foreach ($servers['servers'] as $server => $credentials) {
                $connection = $this->credentials->createConnectionKey($name, $server);
                $filesystem = new Filesystem(new ConnectionKeyAdapter($connection));
                $handle = $connection->toHandle();

                // Mount current server as default remote
                if ($handle === $current) {
                    $this->filesystems['remote'] = $filesystem;
                }

                $this->filesystems[$handle] = $filesystem;
            }
        }
    }
}
