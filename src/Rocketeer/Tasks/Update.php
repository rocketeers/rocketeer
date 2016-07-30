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
 * Update the remote server without doing a new release.
 */
class Update extends Deploy
{
    /**
     * A description of what the task does.
     *
     * @var string
     */
    protected $description = 'Update the remote server without doing a new release';

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $this->steps()->executeStrategyMethod('CreateRelease', 'update');
        $this->steps()->executeTask('PrepareRelease');

        // Run the steps
        if (!$this->runSteps()) {
            return $this->halt();
        }

        // Clear cache
        if (!$this->getOption('no-clear') && $this->getFramework()) {
            $this->getFramework()->clearCache();
        }

        return $this->explainer->success('Successfully updated application');
    }
}
