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

namespace Rocketeer\Strategies\Check;

use Rocketeer\Strategies\AbstractPolyglotStrategy;

class PolyglotStrategy extends AbstractPolyglotStrategy implements CheckStrategyInterface
{
    /**
     * The type of the sub-strategies.
     *
     * @var string
     */
    protected $type = 'Check';

    /**
     * @var string
     */
    protected $description = 'Checks if the server is ready to receive an application of any language';

    /**
     * The various strategies to call.
     *
     * @var array
     */
    protected $strategies = ['Node', 'Php', 'Ruby'];

    /**
     * Check that the PM that'll install
     * the app's dependencies is present.
     *
     * @return bool
     */
    public function manager()
    {
        return $this->checkStrategiesMethod('manager');
    }

    /**
     * Check that the language used by the
     * application is at the required version.
     *
     * @return bool
     */
    public function language()
    {
        return $this->checkStrategiesMethod('language');
    }

    /**
     * Check for the required extensions.
     *
     * @return array
     */
    public function extensions()
    {
        return $this->checkStrategiesMethod('extensions');
    }
}
