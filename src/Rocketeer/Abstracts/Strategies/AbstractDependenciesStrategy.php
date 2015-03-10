<?php
/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Rocketeer\Abstracts\Strategies;

use Illuminate\Container\Container;
use Rocketeer\Abstracts\AbstractPackageManager;

/**
 * Abstract class for Dependencies strategies.
 *
 * @author Maxime Fabre <ehtnam6@gmail.com>
 */
abstract class AbstractDependenciesStrategy extends AbstractStrategy
{
    /**
     * The name of the binary.
     *
     * @type string
     */
    protected $binary;

    /**
     * @type array
     */
    protected $options = [
        'shared_dependencies' => false,
    ];

    /**
     * The package manager instance.
     *
     * @type AbstractPackageManager
     */
    protected $manager;

    /**
     * @param Container $app
     */
    public function __construct(Container $app)
    {
        $this->app     = $app;
        $this->manager = $this->binary($this->binary);
    }

    /**
     * @param AbstractPackageManager $manager
     */
    public function setManager($manager)
    {
        $this->manager = $manager;
    }

    /**
     * Get an instance of the Binary.
     *
     * @return AbstractPackageManager
     */
    protected function getManager()
    {
        return $this->manager;
    }

    /**
     * Whether this particular strategy is runnable or not.
     *
     * @return bool
     */
    public function isExecutable()
    {
        return $this->manager->isExecutable();
    }

    //////////////////////////////////////////////////////////////////////
    ////////////////////////////// COMMANDS //////////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * Install the dependencies.
     *
     * @return bool
     */
    public function install()
    {
        $this->shareDependenciesFolder();

        return $this->runWithBeforeAfterEvents(function () {
            return $this->manager->runForApplication('install', [], $this->getInstallationOptions('install'));
        });
    }

    /**
     * Update the dependencies.
     *
     * @return bool
     */
    public function update()
    {
        return $this->runWithBeforeAfterEvents(function () {
            return $this->manager->runForApplication('update', [], $this->getInstallationOptions('update'));
        });
    }

    //////////////////////////////////////////////////////////////////////
    ///////////////////////// SHARED DEPENDENCIES ////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * Share the dependencies folder if possible.
     */
    protected function shareDependenciesFolder()
    {
        $folder             = $this->manager->getDependenciesFolder();
        $sharedDependencies = $this->getOption('shared_dependencies', true);
        if (!$sharedDependencies || !$folder) {
            return;
        }

        if ($sharedDependencies === 'copy') {
            $this->bash->copyFromPreviousRelease($folder);
        } else {
            $this->bash->share($folder);
        }
    }

    //////////////////////////////////////////////////////////////////////
    ////////////////////////////// HELPERS ///////////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * Get the options to run Bower with.
     *
     * @param string $command
     *
     * @return array
     */
    protected function getInstallationOptions($command)
    {
        return $this->getFlags($command);
    }
}
