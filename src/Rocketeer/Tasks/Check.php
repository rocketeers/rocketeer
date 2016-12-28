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
 * Check if the server is ready to receive the application.
 */
class Check extends AbstractTask
{
    /**
     * A description of what the task does.
     *
     * @var string
     */
    protected $description = 'Check if the server is ready to receive the application';

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
        $this->steps()->checkVcs();
        if ($this->getStrategy('Check')) {
            $this->steps()->checkManagerMethod('language', 'Checking presence of language');
            $this->steps()->checkManagerMethod('manager', 'Checking presence of package manager');
            $this->steps()->checkManagerMethod('extensions', 'Checking presence of required extensions');
        }

        if (!$this->runSteps()) {
            return $this->halt('Server is not ready to receive application');
        }

        // Display confirmation message
        return $this->explainer->success('Your server is ready to deploy');
    }

    ////////////////////////////////////////////////////////////////////
    /////////////////////////////// CHECKS /////////////////////////////
    ////////////////////////////////////////////////////////////////////

    /**
     * Check the presence of an VCS on the server.
     *
     * @return bool
     */
    public function checkVcs()
    {
        // Cancel if not using any VCS
        if (mb_strtolower($this->config->getContextually('strategies.create-release')) !== 'clone') {
            return true;
        }

        $this->explainer->line('Checking presence of '.$this->vcs->getBinary());
        $results = $this->vcs->run('check');
        $this->toOutput($results);

        $isPresent = $this->status();
        if (!$isPresent) {
            $this->explainer->error($this->vcs->getBinary().' could not be found');
        }

        return $isPresent;
    }

    /**
     * @param string $method
     * @param string $message
     *
     * @return bool
     */
    protected function checkManagerMethod($method, $message)
    {
        $this->explainer->line($message);

        return $this->executeStrategyMethod('Check', $method);
    }
}
