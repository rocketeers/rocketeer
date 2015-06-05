<?php

/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Rocketeer\Strategies\Check;

use Rocketeer\Abstracts\Strategies\AbstractPolyglotStrategy;
use Rocketeer\Interfaces\Strategies\CheckStrategyInterface;

class PolyglotStrategy extends AbstractPolyglotStrategy implements CheckStrategyInterface
{
    /**
     * The various strategies to call.
     *
     * @var array
     */
    protected $strategies = ['Node', 'Php', 'Ruby'];

    /**
     * The type of the sub-strategies.
     *
     * @var string
     */
    protected $type = 'Check';

    /**
     * Check that the PM that'll install
     * the app's dependencies is present.
     *
     * @return bool
     */
    public function manager()
    {
        $this->executeStrategiesMethod('manager');

        return $this->passed();
    }

    /**
     * Check that the language used by the
     * application is at the required version.
     *
     * @return bool
     */
    public function language()
    {
        $this->executeStrategiesMethod('language');

        return $this->passed();
    }

    /**
     * Check for the required extensions.
     *
     * @return array
     */
    public function extensions()
    {
        return $this->gatherMissingFromMethod('extensions');
    }

    /**
     * Check for the required drivers.
     *
     * @return array
     */
    public function drivers()
    {
        return $this->gatherMissingFromMethod('drivers');
    }
}
