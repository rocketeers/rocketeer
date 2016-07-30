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

/**
 * Uses a system of folders current/releases/shared to roll releases.
 */
class RollingStrategy extends AbstractStrategy implements DeployStrategyInterface
{
    /**
     * @var string
     */
    protected $description = 'Uses a system of folders current/releases/shared to roll releases';

    /**
     * {@inheritdoc}
     */
    public function deploy()
    {
        if ($this->connections->getCurrentConnectionKey()->isFtp()) {
            return $this->explainer->error('Rolling strategy is not compatible with FTP connections, use "Upload/Sync" instead');
        }

        // Check if server is ready for deployment
        $this->steps()->setupIfNecessary();
        $this->steps()->executeTask('CreateRelease');
        $this->steps()->executeTask('PrepareRelease');

        return $this->runSteps();
    }
}
