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

use Rocketeer\Traits\ContainerAwareTrait;

/**
 * A fork of the MountManager that mounts the existing
 * connections as filesystems onto itself.
 */
class MountManager extends \League\Flysystem\MountManager
{
    use ContainerAwareTrait;

    /**
     * {@inheritdoc}
     */
    public function getFilesystem($prefix)
    {
        // Rebind current filesystems
        $this->mountCurrentFilesystems();

        return parent::getFilesystem($prefix);
    }

    /**
     * Gather the remote filesystems to mount.
     *
     * @return Filesystem[]
     */
    public function mountCurrentFilesystems()
    {
        $connections = $this->connections->getConnections()->all();

        // Mount current connection as remote://
        $current = $this->connections->getCurrentConnectionKey()->toHandle();
        if (isset($connections[$current])) {
            $connections['remote'] = $connections[$current];
        }

        $this->mountFilesystems($connections);
    }
}
