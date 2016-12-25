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

use Illuminate\Support\Arr;
use Rocketeer\Interfaces\IdentifierInterface;
use Rocketeer\Services\Connections\Shell\Bash;
use Rocketeer\Traits\Properties\ConfigurableTrait;
use Rocketeer\Traits\Properties\HasEventsTrait;
use Rocketeer\Traits\SluggableTrait;
use Rocketeer\Traits\StepsRunnerTrait;

/**
 * Core class for strategies.
 */
abstract class AbstractStrategy extends Bash implements IdentifierInterface
{
    use ConfigurableTrait;
    use SluggableTrait;
    use HasEventsTrait;
    use StepsRunnerTrait;

    /**
     * @var array
     */
    protected $options = [];

    /**
     * @var string
     */
    protected $description;

    /**
     * Get the name of the entity.
     *
     * @return string
     */
    public function getName()
    {
        return str_replace('Strategy', null, class_basename($this));
    }

    /**
     * Get the type of strategy.
     *
     * @return string
     */
    public function getType()
    {
        $name = get_class($this);
        $name = explode('\\', $name);
        $name = $name[count($name) - 2];

        return $name;
    }

    /**
     * Get a global identifier for this entity.
     *
     * @return string
     */
    public function getIdentifier()
    {
        return 'strategies.'.mb_strtolower($this->getType()).'.'.$this->getSlug();
    }

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

        $name = Arr::get($components, count($components) - 1);
        $strategy = Arr::get($components, count($components) - 2);

        $parent = ucfirst($strategy);
        $concrete = str_replace('Strategy', null, $name);
        $details = $this->getDescription();

        $this->explainer->display($parent.'/'.$concrete, $details);

        return $this;
    }
}
