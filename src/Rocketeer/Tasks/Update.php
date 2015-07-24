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
 * Update the remote server without doing a new release.
 *
 * @author Maxime Fabre <ehtnam6@gmail.com>
 */
class Update extends Deploy
{
    /**
     * A description of what the task does.
     *
     * @type string
     */
    protected $description = 'Update the remote server without doing a new release';

    /**
     * Run the task.
     *
     * @return bool|null
     */
    public function execute()
    {
        // Check if local is ready for deployment
        if (!$this->executeTask('Primer')) {
            return $this->halt('Project is not ready for deploy. You were almost fired.');
        }

        // Update repository
        if (!$this->getStrategy('Deploy')->update()) {
            return $this->halt();
        }

        // Recreate symlinks if necessary
        $this->steps()->syncSharedFolders();

        // Recompile dependencies and stuff
        $this->steps()->executeTask('Dependencies');

        // Set permissions
        $this->steps()->setApplicationPermissions();

        // Run migrations
        if ($this->getOption('migrate') || $this->getOption('seed')) {
            $this->steps()->executeTask('Migrate');
        }

        // Run the steps
        if (!$this->runSteps()) {
            return $this->halt();
        }

        // Clear cache
        if (!$this->getOption('no-clear')) {
            $this->artisan()->runForCurrentRelease('clearCache');
        }

        $this->command->info('Successfully updated application');
    }
}
