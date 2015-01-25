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
        $this->checkScm();
        $this->checkPackageManagers($check);
        $this->checkLanguages($check);
        $this->checkExtensions($check);
        $this->checkDrivers($check);

        // Return false if any error
        if (!empty($this->errors)) {
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
     */
    public function checkScm()
    {
        // Cancel if not using any SCM
        if ($this->rocketeer->getOption('strategies.deploy') === 'sync') {
            return;
        }

        $this->explainer->line('Checking presence of '.$this->scm->getBinary());
        $results = $this->scm->run('check');
        $this->toOutput($results);

        if ($this->getConnection()->status() !== 0) {
            $this->errors[] = $this->scm->getBinary().' could not be found';
        }
    }

    /**
     * @param AbstractCheckStrategy $check
     */
    protected function checkPackageManagers(AbstractCheckStrategy $check)
    {
        $manager = class_basename($check->getManager());
        $manager = str_replace('Strategy', null, $manager);
        $this->explainer->line('Checking presence of '.$manager);

        if (!$check->manager()) {
            $this->errors[] = sprintf('The %s package manager could not be found', $manager);
        }
    }

    /**
     * @param AbstractCheckStrategy $check
     */
    protected function checkLanguages(AbstractCheckStrategy $check)
    {
        $language = $check->getLanguage();
        $this->explainer->line('Checking '.$language.' version');

        if (!$check->language()) {
            $this->errors[] = $language.' is not at the required version';
        }
    }

    /**
     * @param AbstractCheckStrategy $check
     */
    protected function checkExtensions(AbstractCheckStrategy $check)
    {
        $this->explainer->line('Checking presence of required extensions');
        $extensions = $check->extensions();

        if (!empty($extensions)) {
            $this->errors[] = 'The following extensions could not be found: '.implode(', ', $extensions);
        }
    }

    /**
     * @param AbstractCheckStrategy $check
     */
    protected function checkDrivers(AbstractCheckStrategy $check)
    {
        $this->explainer->line('Checking presence of required drivers');
        $drivers = $check->drivers();

        if (!empty($drivers)) {
            $this->errors[] = 'The following drivers could not be found: '.implode(', ', $drivers);
        }
    }
}
