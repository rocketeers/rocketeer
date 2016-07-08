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

use Rocketeer\Dummies\Strategies\ExecutableStrategy;
use Rocketeer\Dummies\Strategies\NonExecutableStrategy;
use Rocketeer\Strategies\AbstractPolyglotStrategy;

class ExecutablesPolyglotStrategy extends AbstractPolyglotStrategy
{
    /**
     * The various strategies to call.
     *
     * @var array
     */
    protected $strategies = [
        NonExecutableStrategy::class,
        ExecutableStrategy::class,
    ];

    protected $options = [
        'foo' => 'baz',
    ];

    public function fire()
    {
        return $this->executeStrategiesMethod('fire');
    }
}
