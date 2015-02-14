<?php
namespace Rocketeer\Dummies\Strategies;

use Rocketeer\Abstracts\Strategies\AbstractStrategy;

class ExecutableStrategy extends AbstractStrategy
{
    public function fire()
    {
        echo 'executable';
    }
}
