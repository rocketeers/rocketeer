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

use Rocketeer\Strategies\AbstractStrategy;

class RollingStrategy extends AbstractStrategy implements DeployStrategyInterface
{
    /**
     * {@inheritdoc}
     */
    public function deploy()
    {
        if ($this->connections->getCurrentConnectionKey()->isFtp()) {
            return $this->explainer->error('Rolling strategy is not compatible with FTP connections');
        }

        $this->steps()->executeTask('CreateRelease');
        $this->steps()->executeTask('Dependencies');

        if ($this->getOption('tests')) {
            $this->steps()->executeTask('Test');
        }

        // Create release and set permissions
        $this->steps()->setApplicationPermissions();

        // Run migrations
        if ($this->getOption('migrate') || $this->getOption('seed')) {
            $this->steps()->executeTask('Migrate');
        }

        // Synchronize shared folders and files
        $this->steps()->syncSharedFolders();

        // Run the steps until one fails
        if (!$this->runSteps()) {
            return $this->halt();
        }

        // Swap symlink
        if ($this->getOption('coordinated', true)) {
            $swap = $this->coordinator->whenAllServersReadyTo('symlink', 'SwapSymlink');
        } else {
            $swap = $this->executeTask('SwapSymlink');
        }

        return $swap ?: $this->halt();
    }

    ////////////////////////////////////////////////////////////////////
    /////////////////////////////// HELPERS ////////////////////////////
    ////////////////////////////////////////////////////////////////////

    /**
     * Set permissions for the folders used by the application.
     *
     * @return bool
     */
    protected function setApplicationPermissions()
    {
        $files = (array) $this->config->getContextually('remote.permissions.files');
        foreach ($files as &$file) {
            $this->setPermissions($file);
        }

        return true;
    }
}
