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

namespace Rocketeer\Dummies;

use Rocketeer\Abstracts\Strategies\AbstractPolyglotStrategy;

class FailingPolyglotStrategy extends AbstractPolyglotStrategy
{
    /**
     * The various strategies to call.
     *
     * @var array
     */
    protected $strategies = [
        'Rocketeer\Dummies\Strategies\FailingStrategy',
        'Rocketeer\Dummies\Strategies\ExecutableStrategy',
    ];

    public function fire()
    {
        return $this->executeStrategiesMethod('fire');
    }
}
