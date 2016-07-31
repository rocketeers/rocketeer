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

namespace Rocketeer\Strategies\CreateRelease;

use Rocketeer\Strategies\AbstractStrategy;

/**
 * Clones a fresh instance of the repository by VCS.
 */
class CloneStrategy extends AbstractStrategy implements CreateReleaseStrategyInterface
{
    /**
     * @var string
     */
    protected $description = 'Clones a fresh instance of the repository by VCS';

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

        // Executing checkout
        $this->explainer->line('Cloning repository in "'.$destination.'"');
        $output = $this->vcs->run('checkout', $destination);

        // Cancel if failed and forget credentials
        $success = $this->bash->displayStatusMessage('Unable to clone the repository', $output) !== false;
        if (!$success) {
            $this->localStorage->forget('credentials');

            return false;
        }

        // Deploy submodules
        if ($this->config->getContextually('vcs.submodules') && $this->vcs->submodules()) {
            $this->explainer->line('Initializing submodules if any');
            $this->vcs->runForCurrentRelease('submodules');
            $success = $this->status();
        }

        return $success;
    }

    /**
     * Update the latest version of the application.
     *
     * @param bool $reset
     *
     * @return string
     */
    public function update($reset = true)
    {
        $this->explainer->info('Pulling changes');
        $tasks = [$this->vcs->update()];

        // Reset if requested
        if ($reset) {
            array_unshift($tasks, $this->vcs->reset());
        }

        return $this->bash->runForCurrentRelease($tasks);
    }
}
