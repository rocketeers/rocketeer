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

use Rocketeer\Tasks\AbstractTask;

/**
 * Creates a new release on the server.
 */
class CreateRelease extends AbstractTask
{
    /**
     * A description of what the task does.
     *
     * @var string
     */
    protected $description = 'Creates a new release on the server';

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $status = $this->executeStrategyMethod('CreateRelease', 'deploy');

        // Append newly created release to list of existing ones
        $this->releasesManager->addRelease(
            $this->releasesManager->getCurrentRelease()
        );

        return $status;
    }
}
