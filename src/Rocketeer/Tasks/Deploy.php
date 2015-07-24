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

use Rocketeer\Abstracts\AbstractTask;

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
     * @type string
     */
    protected $description = 'Deploys the website';

    /**
     * Run the task.
     *
     * @return bool|null
     */
    public function execute()
    {
        // Check if server is ready for deployment
        if (!$this->isSetup()) {
            $this->explainer->error('Server is not ready, running Setup task');
            $this->executeTask('Setup');
        }

        // Check if local is ready for deployment
        if (!$this->executeTask('Primer')) {
            return $this->halt('Project is not ready for deploy. You were almost fired.');
        }

        // Setup the new release
        $release = $this->releasesManager->getNextRelease();

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

        // Run before-symlink events
        $this->steps()->fireEvent('before-symlink');

        // Update symlink
        $this->steps()->updateSymlink();

        // Run the steps until one fails
        if (!$this->runSteps()) {
            return $this->halt();
        }

        $this->releasesManager->markReleaseAsValid($release);

        $this->explainer->line('Successfully deployed release '.$release);
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
        $files = (array) $this->rocketeer->getOption('remote.permissions.files');
        foreach ($files as &$file) {
            $this->setPermissions($file);
        }

        return true;
    }
}
