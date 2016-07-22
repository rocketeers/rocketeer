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
 *
 * @author Maxime Fabre <ehtnam6@gmail.com>
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
        // Check if server is ready for deployment
        if (!$this->isSetup()) {
            $this->explainer->error('Server is not ready, running Setup task');
            $this->executeTask('Setup');
        }

        // If it's friday, display a motivational message
        if (date('N') === '5') {
            $this->executeTask('FridayDeploy');
        }

        // Setup the new release
        $this->releasesManager->getNextRelease();

        // Create release and set it up
        return $this->getStrategy('Deploy')->deploy();
    }
}
