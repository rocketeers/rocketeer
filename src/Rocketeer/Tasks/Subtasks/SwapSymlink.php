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

namespace Rocketeer\Tasks\Subtasks;

use Rocketeer\Services\Connections\Coordinator;
use Rocketeer\Tasks\AbstractTask;

/**
 * Swaps the symlink on the server.
 */
class SwapSymlink extends AbstractTask
{
    /**
     * A description of what the task does.
     *
     * @var string
     */
    protected $description = 'Swaps the symlink on the server';

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $this->updateSymlink();
        $status = $this->status();

        if ($status) {
            $release = $this->releasesManager->getNextRelease();

            $this->coordinator->setStatus('symlink', Coordinator::DONE);
            $this->releasesManager->markReleaseAsValid($release);
            $this->explainer->line('Successfully deployed release '.$release);
        } else {
            $current = $this->config->get('remote.directories.current');

            $this->explainer->error('Unable to set symlink on '.$current.'/ folder');
        }

        return $status;
    }
}
