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

namespace Rocketeer\Strategies\Dependencies;

use Rocketeer\Services\Container\Container;
use Rocketeer\Strategies\AbstractStrategy;
use Rocketeer\Strategies\HasBinaryTrait;

/**
 * Abstract class for Dependencies strategies.
 */
abstract class AbstractDependenciesStrategy extends AbstractStrategy
{
    use HasBinaryTrait;

    /**
     * @var array
     */
    protected $options = [
        'shared_dependencies' => false,
    ];

    /**
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Whether this particular strategy is runnable or not.
     *
     * @return bool
     */
    public function isExecutable()
    {
        return $this->getBinary()->isExecutable();
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
            return $this->getBinary()->runForApplication('install', [], $this->getInstallationOptions('install'));
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
            return $this->getBinary()->runForApplication('update', [], $this->getInstallationOptions('update'));
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
        $folder = $this->getBinary()->getDependenciesFolder();
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
