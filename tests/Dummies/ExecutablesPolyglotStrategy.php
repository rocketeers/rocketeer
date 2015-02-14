<?php
namespace Rocketeer\Dummies;

use Rocketeer\Abstracts\Strategies\AbstractPolyglotStrategy;

class ExecutablesPolyglotStrategy extends AbstractPolyglotStrategy
{
    /**
     * The various strategies to call
     *
     * @type array
     */
    protected $strategies = array(
        'Rocketeer\Dummies\Strategies\NonExecutableStrategy',
        'Rocketeer\Dummies\Strategies\ExecutableStrategy',
    );

    public function fire()
    {
        return $this->executeStrategiesMethod('fire');
    }
}
