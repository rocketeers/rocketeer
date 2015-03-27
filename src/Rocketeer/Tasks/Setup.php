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
 * Set up the remote server for deployment.
 *
 * @author Maxime Fabre <ehtnam6@gmail.com>
 */
class Setup extends AbstractTask
{
    /**
     * A description of what the task does.
     *
     * @type string
     */
    protected $description = 'Set up the remote server for deployment';

    /**
     * Whether the task needs to be run on each stage or globally.
     *
     * @type bool
     */
    public $usesStages = false;

    /**
     * Run the task.
     *
     * @return string|false|null
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
        $application = $this->rocketeer->getApplicationName();
        $homeFolder  = $this->paths->getHomeFolder();
        $message     = sprintf('Successfully setup "%s" at "%s"', $application, $homeFolder);

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
        $availableStages = $this->connections->getStages();
        $originalStage   = $this->connections->getStage();
        if (empty($availableStages)) {
            $availableStages = [null];
        }

        // Create folders
        foreach ($availableStages as $stage) {
            $this->connections->setStage($stage);
            $this->createFolder('releases', true);
            $this->createFolder('current', true);
            $this->createFolder('shared', true);
        }

        if ($originalStage) {
            $this->connections->setStage($originalStage);
        }
    }
}
