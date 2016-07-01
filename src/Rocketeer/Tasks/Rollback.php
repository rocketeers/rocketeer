<?php

/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Rocketeer\Tasks;

/**
 * Rollback to the previous release, or to a specific one.
 *
 * @author Maxime Fabre <ehtnam6@gmail.com>
 */
class Rollback extends AbstractTask
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Rollback to the previous release, or to a specific one';

    /**
     * Run the task.
     *
     * @return string|null
     */
    public function execute()
    {
        $releases = $this->releasesManager->getReleases();
        if (empty($releases)) {
            return $this->explainer->error('Rocketeer could not rollback as no releases have yet been deployed');
        }

        // Get previous release
        $rollbackRelease = $this->getRollbackRelease($releases);
        if (!$rollbackRelease) {
            return $this->explainer->error('Rocketeer could not rollback as no releases have yet been deployed');
        }

        // If no release specified, display the available ones
        if ($this->command->option('list')) {
            $this->displayReleases();

            // Get actual release name from date
            $rollbackRelease = $this->command->askWith('Which one do you want to go back to ?', 0);
            $rollbackRelease = $releases[$rollbackRelease];
        }

        // Check if release actually exists
        if (!in_array($rollbackRelease, $releases, true)) {
            return $this->explainer->error('Unable to find release:'.$rollbackRelease);
        }

        // Rollback release
        $this->explainer->success('Rolling back to release '.$rollbackRelease);
        $this->updateSymlink($rollbackRelease);
    }

    ////////////////////////////////////////////////////////////////////
    /////////////////////////////// HELPERS ////////////////////////////
    ////////////////////////////////////////////////////////////////////

    /**
     * Get the release to rollback to.
     *
     * @param  array
     *
     * @return int|null
     */
    protected function getRollbackRelease($releases)
    {
        $release = $this->command->argument('release');
        if (!$release) {
            // Get previous release number from current release path
            $release = $this->bash->getReleaseFromCurrentPath();
            if (!empty($releases)) {
                $releaseKey = array_search($release, $releases);
                if (isset($releases[$releaseKey + 1])) {
                    $release = $releases[$releaseKey + 1];
                }
            }
        }

        return (string) $release;
    }
}
