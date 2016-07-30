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

namespace Rocketeer\Strategies\Deploy;

use Illuminate\Support\Arr;

/**
 * Deploys in local then rsyncs the files on the server.
 */
class SyncStrategy extends AbstractLocalDeployStrategy
{
    /**
     * @var string
     */
    protected $description = 'Deploys in local then rsyncs the files on the server';

    /**
     * @var array
     */
    protected $options = [
        // 'port' => null,
        'excluded' => ['.git', 'vendor'],
    ];

    /**
     * @param string $from
     * @param string $to
     *
     * @return mixed
     */
    protected function onReleaseReady($from, $to)
    {
        // Build host handle
        $arguments = [];
        $handle = $this->getSyncHandle();

        // Create options
        $options = [
            '--verbose' => null,
            '--recursive' => null,
            '--compress' => null,
            '--rsh' => $this->getTransport(),
            '--exclude' => $this->getOption('excluded', true),
        ];

        // Build arguments
        $arguments[] = $from;
        $arguments[] = $handle.':'.$to;

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
            $ssh .= ' -i "'.$key.'"';
        }

        return $ssh;
    }
}
