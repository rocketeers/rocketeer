<?php
namespace Rocketeer\Dummies;

use Rocketeer\Abstracts\Strategies\AbstractPolyglotStrategy;

class FailingPolyglotStrategy extends AbstractPolyglotStrategy
{
    /**
     * The various strategies to call.
     *
     * @type array
     */
    protected $strategies = array(
        'Rocketeer\Dummies\Strategies\FailingStrategy',
        'Rocketeer\Dummies\Strategies\ExecutableStrategy',
    );

    public function fire()
    {
        return $this->executeStrategiesMethod('fire');
    }
}
