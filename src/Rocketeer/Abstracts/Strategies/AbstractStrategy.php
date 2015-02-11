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
use Rocketeer\Interfaces\IdentifierInterface;
use Rocketeer\Traits\Properties\Configurable;
use Rocketeer\Traits\Sluggable;

/**
 * Core class for strategies
 *
 * @author Maxime Fabre <ehtnam6@gmail.com>
 */
abstract class AbstractStrategy extends Bash implements IdentifierInterface
{
    use Configurable;
    use Sluggable;

    /**
     * @type array
     */
    protected $options = [];

    /**
     * @type string
     */
    protected $description;

    /**
     * Get the name of the entity
     *
     * @return string
     */
    public function getName()
    {
        $name = class_basename($this);
        $name = str_replace('Strategy', null, $name);

        return $name;
    }

    /**
     * Get a global identifier for this entity
     *
     * @return string
     */
    public function getIdentifier()
    {
        return 'strategies.'.$this->getSlug();
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Whether this particular strategy is runnable or not
     *
     * @return boolean
     */
    public function isExecutable()
    {
        return true;
    }

    //////////////////////////////////////////////////////////////////////
    ////////////////////////////// HELPERS ///////////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * Display what the command is and does
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
