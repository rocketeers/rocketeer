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
     * @type array
     */
    protected $options = array(
        'port'     => null,
        'excluded' => ['.git', 'vendor'],
    );

    /**
     * Deploy a new clean copy of the application
     *
     * @param string|null $destination
     *
     * @return boolean
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
     * Update the latest version of the application
     *
     * @param boolean $reset
     *
     * @return boolean
     */
    public function update($reset = true)
    {
        $release = $this->releasesManager->getCurrentReleasePath();

        return $this->rsyncTo($release);
    }

    /**
     * Rsyncs the local folder to a remote one
     *
     * @param string $destination
     *
     * @return boolean
     */
    protected function rsyncTo($destination)
    {
        // Build host handle
        $arguments = [];
        $handle    = $this->getSyncHandle();

        // Create options
        $options = ['--verbose' => null, '--recursive' => null, '--rsh' => 'ssh'];
        if ($port = $this->getOption('port', true)) {
            $options['--rsh'] = 'ssh -p '.$port;
        }

        // Build arguments
        $arguments[] = './';
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
     * Get the handle to connect with
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
            $this->options['port'] = $explodedHandle[1];
            $handle                = $explodedHandle[0];
        }

        // Add username
        if ($user = array_get($credentials, 'username')) {
            $handle = $user.'@'.$handle;
        }

        return $handle;
    }
}
