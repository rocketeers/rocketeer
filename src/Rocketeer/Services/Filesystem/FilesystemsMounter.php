<?php
namespace Rocketeer\Services\Filesystem;

use League\Flysystem\Filesystem;
use League\Flysystem\MountManager;
use Rocketeer\Traits\HasLocator;

class FilesystemsMounter
{
    use HasLocator;

    /**
     * @type ConnectionKeyAdapter[]
     */
    protected $filesystems;

    /**
     * @return MountManager
     */
    public function getMountManager()
    {
        $this->filesystems = ['local' => $this->files];

        if ($this->app->bound('rocketeer.connections') && $this->connections->hasCurrentConnection()) {
            $this->gatherRemoteFilesystems();
        }

        return new MountManager($this->filesystems);
    }

    /**
     * Gather the remote filesystems to mount
     */
    protected function gatherRemoteFilesystems()
    {
        $connections = $this->connections->getAvailableConnections();

        foreach ($connections as $name => $servers) {
            foreach ($servers['servers'] as $server => $credentials) {
                $connection = $this->credentials->createConnectionKey($name, $server);
                $root       = $this->rocketeer->getOption('remote.root_directory', $connection);
                $adapter    = new ConnectionKeyAdapter($connection, $root);
                $filesystem = new Filesystem($adapter);

                $this->filesystems[$connection->toHandle()] = $filesystem;
            }
        }
    }
}
