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

namespace Rocketeer\Tasks;

/**
 * Deploy the website.
 */
class Deploy extends AbstractTask
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Deploys the website';

    /**
     * @var array
     */
    protected $options = [
        'coordinated' => false,
    ];

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        // If it's friday, display a motivational message
        if (date('N') === '5') {
            $this->executeTask('FridayDeploy');
        }

        // Setup the new release
        $this->releasesManager->getNextRelease();

        return $this->executeStrategyMethod('Deploy', 'deploy') ?: $this->halt();
    }
}
