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

namespace Rocketeer\Strategies;

use Rocketeer\Container;

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
     * @var string
     */
    protected $binary;

    /**
     * @var array
     */
    protected $options = [
        'shared_dependencies' => false,
    ];

    /**
     * The package manager instance.
     *
     * @var \Rocketeer\Binaries\PackageManagers\AbstractPackageManager
     */
    protected $manager;

    /**
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->manager = $this->binary($this->binary);
    }

    /**
     * @param \Rocketeer\Binaries\PackageManagers\AbstractPackageManager $manager
     */
    public function setManager($manager)
    {
        $this->manager = $manager;
    }

    /**
     * Get an instance of the Binary.
     *
     * @return \Rocketeer\Binaries\PackageManagers\AbstractPackageManager
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
        $folder = $this->manager->getDependenciesFolder();
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
