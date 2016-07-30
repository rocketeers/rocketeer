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

use League\Container\ContainerAwareInterface;
use Rocketeer\Traits\ContainerAwareTrait;

/**
 * A class for modules to be registered with a modulable.
 */
abstract class AbstractModule implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * @var ModulableInterface
     */
    protected $modulable;

    /**
     * @var bool
     */
    protected $default = false;

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

    /**
     * @return bool
     */
    public function isDefault()
    {
        return $this->default;
    }

    /**
     * @return string[]
     */
    abstract public function getProvided();
}
