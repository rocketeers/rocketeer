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
        return $this->manager->runForCurrentRelease('install');
    }

    /**
     * Update the dependencies.
     *
     * @return bool
     */
    public function update()
    {
        return $this->manager->runForCurrentRelease('update');
    }
}
