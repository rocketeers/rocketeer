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
use Rocketeer\Interfaces\Strategies\DeployStrategyInterface;

class CloneStrategy extends AbstractStrategy implements DeployStrategyInterface
{
    /**
     * @type string
     */
    protected $description = 'Clones a fresh instance of the repository by SCM';

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
        $output = $this->scm->run('checkout', $destination);

        // Cancel if failed and forget credentials
        $success = $this->bash->checkStatus('Unable to clone the repository', $output) !== false;
        if (!$success) {
            $this->localStorage->forget('credentials');

            return false;
        }

        // Deploy submodules
        if ($this->rocketeer->getOption('scm.submodules') && $this->scm->submodules()) {
            $this->explainer->line('Initializing submodules if any');
            $this->scm->runForCurrentRelease('submodules');
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
        $this->command->info('Pulling changes');
        $tasks = [$this->scm->update()];

        // Reset if requested
        if ($reset) {
            array_unshift($tasks, $this->scm->reset());
        }

        return $this->bash->runForCurrentRelease($tasks);
    }
}
