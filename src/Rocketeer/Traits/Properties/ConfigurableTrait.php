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

namespace Rocketeer\Traits\Properties;

use InvalidArgumentException;

/**
 * A class that can be configured via a set of options.
 */
trait ConfigurableTrait
{
    //////////////////////////////////////////////////////////////////////
    //////////////////////// SETTERS AND GETTERS /////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Configure the task.
     *
     * @param array $options
     *
     * @return $this
     */
    public function configure(array $options)
    {
        $this->options = array_merge($this->options, $options);

        return $this;
    }

    /**
     * @param array $options
     *
     * @return $this
     */
    public function setOptions(array $options)
    {
        $this->options = $options;

        return $this;
    }

    //////////////////////////////////////////////////////////////////////
    /////////////////////////////// FLAGS ////////////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * Set flags on the command.
     *
     * @param array $flags
     */
    public function setFlags(array $flags)
    {
        // Check types of flags
        $types = array_filter($flags, 'is_array');
        if (count($types) !== count($flags)) {
            throw new InvalidArgumentException('Flags must be passed as an array');
        }

        $this->options['flags'] = $flags;
    }

    /**
     * Get the flags for a command.
     *
     * @param string|null $command
     *
     * @return array
     */
    public function getFlags($command = null)
    {
        $option = $command ? 'flags.'.$command : 'flags';

        return $this->getOption($option, true);
    }

    //////////////////////////////////////////////////////////////////////
    ////////////////////////////// FETCHING //////////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * Get a callable option.
     *
     * @param string $option
     *
     * @return callable|null
     */
    protected function getClosureOption($option)
    {
        $option = array_get($this->options, $option);
        if (!is_callable($option)) {
            return;
        }

        return $option;
    }
}
