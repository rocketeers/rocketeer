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

use Rocketeer\Interfaces\Strategies\DeployStrategyInterface;

class CopyStrategy extends CloneStrategy implements DeployStrategyInterface
{
    /**
     * @type string
     */
    protected $description = 'Copies the previously cloned instance of the repository and update it';

    /**
     * Deploy a new clean copy of the application.
     *
     * @param string|null $destination
     *
     * @return bool|string
     */
    public function deploy($destination = null)
    {
        // Get the previous release, if none clone from scratch
        $previous = $this->releasesManager->getReleases();
        if (!$previous) {
            return parent::deploy($destination);
        }

        // If we have a previous release, check its validity
        $previous = $this->releasesManager->getPreviousRelease();
        $previous = $this->releasesManager->getPathToRelease($previous);
        if (!$previous) {
            return parent::deploy($destination);
        }

        // Recompute destination
        if (!$destination) {
            $destination = $this->releasesManager->getCurrentReleasePath();
        }

        // Copy old release into new one
        $this->explainer->success('Copying previous release "'.$previous.'" in "'.$destination.'"');
        $this->bash->copy($previous, $destination);

        // Update repository
        return $this->update();
    }
}
