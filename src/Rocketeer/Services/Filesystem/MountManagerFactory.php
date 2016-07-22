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

use League\Flysystem\MountManager;
use Rocketeer\Traits\ContainerAwareTrait;

class MountManagerFactory
{
    use ContainerAwareTrait;

    /**
     * @return MountManager
     */
    public function getMountManager()
    {
        return new MountManager($this->getFilesystems());
    }

    /**
     * Gather the remote filesystems to mount.
     *
     * @return Filesystem[]
     */
    protected function getFilesystems()
    {
        $connections = $this->connections->getConnections();
        $current = $this->connections->getCurrentConnectionKey()->toHandle();

        $filesystems = [];
        foreach ($connections as $handle => $connection) {
            // Mount current server as default remote
            if ($handle === $current) {
                $filesystems['remote'] = $connection;
            }

            $filesystems[$handle] = $connection;
        }

        return $filesystems;
    }
}
