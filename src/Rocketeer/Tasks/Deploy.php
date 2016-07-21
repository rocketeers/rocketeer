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
            dump('lol');
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
