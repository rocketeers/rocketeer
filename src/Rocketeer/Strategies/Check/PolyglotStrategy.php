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

/**
 * Checks if the server is ready to receive an application of any language.
 */
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
     * {@inheritdoc}
     */
    public function manager()
    {
        return $this->checkStrategiesMethod('manager');
    }

    /**
     * {@inheritdoc}
     */
    public function language()
    {
        return $this->checkStrategiesMethod('language');
    }

    /**
     * {@inheritdoc}
     */
    public function extensions()
    {
        return $this->checkStrategiesMethod('extensions');
    }
}
