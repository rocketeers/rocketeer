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

namespace Rocketeer\Services\Modules;

abstract class AbstractModule
{
    /**
     * @var ModulableInterface
     */
    protected $modulable;

    /**
     * @return string[]
     */
    abstract public function getProvided();

    /**
     * @return ModulableInterface
     */
    public function getModulable()
    {
        return $this->modulable;
    }

    /**
     * @param ModulableInterface $modulable
     */
    public function setModulable($modulable)
    {
        $this->modulable = $modulable;
    }
}
