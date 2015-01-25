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
use Rocketeer\Abstracts\Strategies\AbstractCheckStrategy;

/**
 * Check if the server is ready to receive the application
 *
 * @author Maxime Fabre <ehtnam6@gmail.com>
 */
class Check extends AbstractTask
{
    /**
     * A description of what the task does
     *
     * @var string
     */
    protected $description = 'Check if the server is ready to receive the application';

    /**
     * Whether the task needs to be run on each stage or globally
     *
     * @var boolean
     */
    public $usesStages = false;

    /**
     * The checks that failed
     *
     * @type array
     */
    protected $errors = [];

    /**
     * Run the task
     *
     * @return boolean|null
     */
    public function execute()
    {
        $this->errors = [];

        /** @type AbstractCheckStrategy $check */
        $check = $this->getStrategy('Check');

        // Execute various checks
        $this->steps()->checkScm();
        $this->steps()->checkLanguages($check);
        $this->steps()->checkPackageManagers($check);
        $this->steps()->checkExtensions($check, 'extensions');
        $this->steps()->checkExtensions($check, 'drivers');

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
     * Check the presence of an SCM on the server
     *
     * @return boolean
     */
    public function checkScm()
    {
        // Cancel if not using any SCM
        if ($this->rocketeer->getOption('strategies.deploy') === 'sync') {
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
     * @return boolean
     */
    protected function checkPackageManagers(AbstractCheckStrategy $check)
    {
        $manager = class_basename($check->getManager());
        $manager = str_replace('Strategy', null, $manager);
        $this->explainer->line('Checking presence of '.$manager);

        return $this->executeCheck(
            $check->manager(),
            sprintf('The %s package manager could not be found', $manager)
        );
    }

    /**
     * @param AbstractCheckStrategy $check
     *
     * @return boolean
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
     * @return boolean
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
     * Execute a check and log the error if not
     *
     * @param boolean $condition
     * @param string  $error
     *
     * @return boolean
     */
    protected function executeCheck($condition, $error)
    {
        if (!$condition) {
            $this->errors[] = $error;
        }

        return (bool) $condition;
    }
}
