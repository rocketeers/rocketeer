<?php
namespace Rocketeer\Tasks\Subtasks;

use Rocketeer\Abstracts\AbstractTask;
use Rocketeer\Services\Connections\Coordinator;

class SwapSymlink extends AbstractTask
{
    /**
     * A description of what the task does
     *
     * @type string
     */
    protected $description = 'Swaps the symlink on the server';

    /**
     * Run the task
     *
     * @return string
     */
    public function execute()
    {
        if ($this->updateSymlink()) {
            $release = $this->releasesManager->getNextRelease();

            $this->coordinator->setStatus('symlink', Coordinator::DONE);
            $this->releasesManager->markReleaseAsValid($release);
            $this->explainer->line('Successfully deployed release '.$release);
        }
    }
}
