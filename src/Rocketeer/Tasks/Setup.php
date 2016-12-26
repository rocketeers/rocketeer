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
 * Set up the remote server for deployment.
 */
class Setup extends AbstractTask
{
    /**
     * A description of what the task does.
     *
     * @var string
     */
    protected $description = 'Set up the remote server for deployment';

    /**
     * Whether the task needs to be run on each stage or globally.
     *
     * @var bool
     */
    public $usesStages = false;

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        // Check if requirements are met
        if ($this->executeTask('Check') === false && !$this->getOption('pretend')) {
            return false;
        }

        // Create base folder
        $this->createFolder();
        $this->createStages();

        // Set setup to true
        $this->localStorage->set('is_setup', true);

        // Get server informations
        $this->explainer->line('Getting some informations about the server');
        $this->environment->getSeparator();
        $this->environment->getLineEndings();

        // Create confirmation message
        $application = $this->config->get('application_name');
        $homeFolder = $this->paths->getHomeFolder();
        $message = sprintf('Successfully setup "%s" at "%s"', $application, $homeFolder);

        return $this->explainer->success($message);
    }

    ////////////////////////////////////////////////////////////////////
    /////////////////////////////// HELPERS ////////////////////////////
    ////////////////////////////////////////////////////////////////////

    /**
     * Create the Application's folders.
     */
    protected function createStages()
    {
        // Get stages
        $availableStages = $this->connections->getAvailableStages();
        $originalStage = $this->connections->getCurrentConnectionKey()->stage;
        if (empty($availableStages)) {
            $availableStages = [null];
        }

        // Create folders
        $mapping = $this->config->get('remote.directories');
        foreach ($availableStages as $stage) {
            $this->connections->setStage($stage);
            $this->createFolder($mapping['releases']);
            $this->createFolder($mapping['current']);
            $this->createFolder($mapping['shared']);
        }

        if ($originalStage) {
            $this->connections->setStage($originalStage);
        }
    }
}
