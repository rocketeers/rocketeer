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

abstract class AbstractLocalDeployStrategy extends AbstractStrategy implements DeployStrategyInterface
{
    /**
     * Prepare a release and mark it as deployed.
     *
     * @return bool
     */
    public function deploy()
    {
        $from = $this->on('dummy', function () {
            $this->setupIfNecessary();
            $this->executeTask('CreateRelease');
            $this->executeTask('Dependencies');

            return $this->releasesManager->getCurrentReleasePath();
        });

        $to = $this->connections->getCurrentConnectionKey()->isFtp()
            ? $this->paths->getFolder()
            : $this->releasesManager->getCurrentReleasePath();

        return $this->onReleaseReady($from, $to);
    }

    /**
     * @param string $from
     * @param string $to
     *
     * @return null|string
     */
    abstract protected function onReleaseReady($from, $to);
}
