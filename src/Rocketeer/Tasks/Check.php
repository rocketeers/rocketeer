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

use Rocketeer\Strategies\Check\AbstractCheckStrategy;

/**
 * Check if the server is ready to receive the application.
 *
 * @author Maxime Fabre <ehtnam6@gmail.com>
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
     * The checks that failed.
     *
     * @var array
     */
    protected $errors = [];

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $this->errors = [];
        $this->steps()->checkScm();

        // Execute strategy checks
        /** @var AbstractCheckStrategy $check */
        $check = $this->getStrategy('Check');
        if ($check) {
            $this->steps()->checkLanguages($check);
            $this->steps()->checkPackageManagers($check);
            $this->steps()->checkExtensions($check, 'extensions');
            $this->steps()->checkExtensions($check, 'drivers');
        }

        // Return false if any error
        if (!$this->runSteps()) {
            return $this->halt(implode(PHP_EOL, $this->errors));
        }

        // Display confirmation message
        $this->explainer->line('Your server is ready to deploy');
    }

    ////////////////////////////////////////////////////////////////////
    /////////////////////////////// CHECKS /////////////////////////////
    ////////////////////////////////////////////////////////////////////

    /**
     * Check the presence of an SCM on the server.
     *
     * @return bool
     */
    public function checkScm()
    {
        // Cancel if not using any SCM
        if ($this->config->getContextually('strategies.deploy') === 'sync') {
            return true;
        }

        $this->explainer->line('Checking presence of '.$this->scm->getBinary());
        $results = $this->scm->run('check');
        $this->toOutput($results);

        return $this->executeCheck(
            $this->getConnection()->status() === 0,
            $this->scm->getBinary().' could not be found'
        );
    }

    /**
     * @param AbstractCheckStrategy $check
     *
     * @return bool
     */
    protected function checkPackageManagers(AbstractCheckStrategy $check)
    {
        $manager = $check->getManager();
        $managerName = str_replace('Strategy', null, $manager->getName());
        $this->explainer->line('Checking presence of '.$managerName);

        $message = $manager->hasManifest()
            ? sprintf('The %s package manager could not be found', $managerName)
            : sprintf('No manifest (%s) was found for %s', $manager->getManifest(), $managerName);

        return $this->executeCheck(
            $check->manager(),
            $message
        );
    }

    /**
     * @param AbstractCheckStrategy $check
     *
     * @return bool
     */
    protected function checkLanguages(AbstractCheckStrategy $check)
    {
        $language = $check->getLanguage();
        $this->explainer->line('Checking '.$language.' version');

        return $this->executeCheck(
            $check->language(),
            $language.' is not at the required version'
        );
    }

    /**
     * @param AbstractCheckStrategy $check
     * @param string                $type
     *
     * @return bool
     */
    protected function checkExtensions(AbstractCheckStrategy $check, $type)
    {
        $this->explainer->line('Checking presence of required '.$type);
        $entries = $check->$type();

        return $this->executeCheck(
            empty($entries),
            'The following '.$type.' could not be found: '.implode(', ', $entries)
        );
    }

    //////////////////////////////////////////////////////////////////////
    /////////////////////////////// HEPERS ///////////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * Execute a check and log the error if not.
     *
     * @param bool   $condition
     * @param string $error
     *
     * @return bool
     */
    protected function executeCheck($condition, $error)
    {
        if (!$condition) {
            $this->errors[] = $error;
        }

        return (bool) $condition;
    }
}
