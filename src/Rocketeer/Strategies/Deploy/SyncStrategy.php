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
use Rocketeer\Interfaces\Strategies\DeployStrategyInterface;

class SyncStrategy extends AbstractStrategy implements DeployStrategyInterface
{
    /**
     * @var string
     */
    protected $description = 'Uses rsync to create or update a release from the local files';

    /**
     * @var array
     */
    protected $options = [
        // 'port' => null,
        'excluded' => ['.git', 'vendor'],
    ];

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
     * @param string $source
     *
     * @return bool
     */
    protected function rsyncTo($destination, $source = './')
    {
        // Build host handle
        $arguments = [];
        $handle = $this->getSyncHandle();

        // Create options
        $options = ['--verbose' => null, '--recursive' => null, '--compress' => null, '--rsh' => $this->getTransport()];

        // Build arguments
        $arguments[] = $source;
        $arguments[] = $handle.':'.$destination;

        // Set excluded files and folders
        $options['--exclude'] = ['.git', 'vendor'];

        // Create binary and command
        $rsync = $this->binary('rsync');
        $command = $rsync->getCommand(null, $arguments, $options);

        return $this->bash->runLocally($command);
    }

    /**
     * Get the handle to connect with.
     *
     * @return string
     */
    protected function getSyncHandle()
    {
        $credentials = $this->credentials->getServerCredentials();
        $handle = array_get($credentials, 'host');
        $explodedHandle = explode(':', $handle);

        // Extract port
        if (count($explodedHandle) === 2) {
            $this->options['port'] = $explodedHandle[1];
            $handle = $explodedHandle[0];
        }

        // Add username
        if ($user = array_get($credentials, 'username')) {
            $handle = $user.'@'.$handle;
        }

        return $handle;
    }

    /**
     * Get the transport to use.
     *
     * @return string
     */
    protected function getTransport()
    {
        $ssh = 'ssh';

        // Get port
        if ($port = $this->getOption('port', true)) {
            $ssh .= ' -p '.$port;
        }

        // Get key
        $key = $this->credentials->getServerCredentials();
        $key = Arr::get($key, 'key');
        if ($key) {
            $ssh .= ' -i '.$key;
        }

        return $ssh;
    }
}
