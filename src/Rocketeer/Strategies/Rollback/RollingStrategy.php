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

namespace Rocketeer\Strategies\Rollback;

use Rocketeer\Strategies\AbstractStrategy;

/**
 * Uses a system of folders current/releases/shared to roll releases.
 */
class RollingStrategy extends AbstractStrategy implements RollbackStrategyInterface
{
    /**
     * {@inheritdoc}
     */
    public function rollback()
    {
        $releases = $this->releasesManager->getReleases();

        // Get previous release
        $rollbackRelease = $this->getRollbackRelease();
        if (!$rollbackRelease) {
            return $this->explainer->error('Rocketeer could not rollback as no releases have yet been deployed');
        }

        // If no release specified, display the available ones
        if ($this->command->option('list')) {
            $this->displayReleases();

            // Get actual release name from date
            $rollbackRelease = $this->command->ask('Which one do you want to go back to ?', 0);
            $rollbackRelease = $releases[$rollbackRelease];
        }

        // Check if release actually exists
        if (!in_array($rollbackRelease, $releases, true)) {
            return $this->explainer->error('Unable to find release:'.$rollbackRelease);
        }

        // Rollback release
        $this->updateSymlink($rollbackRelease);

        return $this->explainer->success('Rolling back to release '.$rollbackRelease);
    }

    ////////////////////////////////////////////////////////////////////
    /////////////////////////////// HELPERS ////////////////////////////
    ////////////////////////////////////////////////////////////////////

    /**
     * Get the release to rollback to.
     *
     * @return int|null
     */
    protected function getRollbackRelease()
    {
        $release = $this->command->argument('release');
        if (!$release) {
            $release = $this->releasesManager->getPreviousRelease();
        }

        return (string) $release;
    }
}
