<?php
namespace Rocketeer\Dummies\Strategies;

use Rocketeer\Abstracts\Strategies\AbstractStrategy;

class FailingStrategy extends AbstractStrategy
{
    public function fire()
    {
        return false;
    }
}
