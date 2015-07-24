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

use Illuminate\Support\Arr;
use Rocketeer\Bash;

/**
 * Core class for strategies.
 *
 * @author Maxime Fabre <ehtnam6@gmail.com>
 */
abstract class AbstractStrategy extends Bash
{
    /**
     * @type string
     */
    protected $description;

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Whether this particular strategy is runnable or not.
     *
     * @return bool
     */
    public function isExecutable()
    {
        return true;
    }

    //////////////////////////////////////////////////////////////////////
    ////////////////////////////// HELPERS ///////////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * Display what the command is and does.
     *
     * @return $this
     */
    public function displayStatus()
    {
        // Recompose strategy and implementation from
        // the class name
        $components = get_class($this);
        $components = explode('\\', $components);

        $name     = Arr::get($components, count($components) - 1);
        $strategy = Arr::get($components, count($components) - 2);

        $parent   = ucfirst($strategy);
        $concrete = str_replace('Strategy', null, $name);
        $details  = $this->getDescription();

        $this->explainer->display($parent.'/'.$concrete, $details);

        return $this;
    }
}
