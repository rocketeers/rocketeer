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
 * Check if the server is ready to receive the application.
 *
 * @author Maxime Fabre <ehtnam6@gmail.com>
 */
class Check extends AbstractTask
{
    /**
     * A description of what the task does.
     *
     * @type string
     */
    protected $description = 'Check if the server is ready to receive the application';

    /**
     * Whether the task needs to be run on each stage or globally.
     *
     * @type bool
     */
    public $usesStages = false;

    /**
     * Run the task.
     *
     * @return bool|null
     */
    public function execute()
    {
        $check  = $this->getStrategy('Check');
        $errors = [];

        // Check the depoy strategy
        if ($this->rocketeer->getOption('strategies.deploy') !== 'sync' && !$this->checkScm()) {
            $errors[] = $this->scm->getBinary().' could not be found';
        }

        // Check package manager
        $manager = class_basename($check->getManager());
        $manager = str_replace('Strategy', null, $manager);
        $this->explainer->line('Checking presence of '.$manager);
        if (!$check->manager()) {
            $errors[] = sprintf('The %s package manager could not be found', $manager);
        }

        // Check language
        $language = $check->getLanguage();
        $this->explainer->line('Checking '.$language.' version');
        if (!$check->language()) {
            $errors[] = $language.' is not at the required version';
        }

        // Check extensions
        $this->explainer->line('Checking presence of required extensions');
        $extensions = $check->extensions();
        if (!empty($extensions)) {
            $errors[] = 'The following extensions could not be found: '.implode(', ', $extensions);
        }

        // Check drivers
        $this->explainer->line('Checking presence of required drivers');
        $drivers = $check->drivers();
        if (!empty($drivers)) {
            $errors[] = 'The following drivers could not be found: '.implode(', ', $drivers);
        }

        // Return false if any error
        if (!empty($errors)) {
            return $this->halt(implode(PHP_EOL, $errors));
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
        $this->explainer->line('Checking presence of '.$this->scm->getBinary());
        $results = $this->scm->run('check');
        $this->toOutput($results);

        return $this->getConnection()->status() === 0;
    }
}
