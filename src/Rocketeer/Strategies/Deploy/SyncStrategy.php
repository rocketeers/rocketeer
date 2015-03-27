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

use Illuminate\Support\Arr;
use Rocketeer\Abstracts\Strategies\AbstractStrategy;
use Rocketeer\Bash;
use Rocketeer\Interfaces\Strategies\DeployStrategyInterface;

class SyncStrategy extends AbstractStrategy implements DeployStrategyInterface
{
    /**
     * @type string
     */
    protected $description = 'Uses rsync to create or update a release from the local files';

    /**
     * @type int
     */
    protected $port;

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

        return $this->rsyncTo($destination);
    }

    /**
     * Update the latest version of the application.
     *
     * @param bool $reset
     *
     * @return bool
     */
    public function update($reset = true)
    {
        $release = $this->releasesManager->getCurrentReleasePath();

        return $this->rsyncTo($release);
    }

    /**
     * Rsyncs the local folder to a remote one.
     *
     * @param string $destination
     *
     * @return bool
     */
    protected function rsyncTo($destination, $source = './')
    {
        // Build host handle
        $arguments = [];
        $handle    = $this->getSyncHandle();

        // Create options
        $options = ['--verbose' => null, '--recursive' => null, '--rsh' => 'ssh', '--compress' => null];

        // Create SSH command
        $options['--rsh'] = $this->getTransport();

        // Build arguments
        $arguments[] = $source;
        $arguments[] = $handle.':'.$destination;

        // Set excluded files and folders
        $options['--exclude'] = ['.git', 'vendor'];

        // Create binary and command
        $rsync   = $this->binary('rsync');
        $command = $rsync->getCommand(null, $arguments, $options);

        return $this->bash->onLocal(function (Bash $bash) use ($command) {
            return $bash->run($command);
        });
    }

    /**
     * Get the handle to connect with.
     *
     * @return string
     */
    protected function getSyncHandle()
    {
        $credentials    = $this->connections->getServerCredentials();
        $handle         = array_get($credentials, 'host');
        $explodedHandle = explode(':', $handle);

        // Extract port
        if (count($explodedHandle) === 2) {
            $this->port = $explodedHandle[1];
            $handle     = $explodedHandle[0];
        }

        // Add username
        if ($user = array_get($credentials, 'username')) {
            $handle = $user.'@'.$handle;
        }

        return $handle;
    }

    /**
     * @return string
     */
    protected function getTransport()
    {
        $ssh = 'ssh';

        // Get port
        if ($port = $this->getOption('port', true) ?: $this->port) {
            $ssh .= ' -p '.$port;
        }

        // Get key
        $key = $this->connections->getServerCredentials();
        $key = Arr::get($key, 'key');
        if ($key) {
            $ssh .= ' -i '.$key;
        }

        return $ssh;
    }
}
