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

use Rocketeer\Traits\StepsRunnerTrait;

class DummyWithSteps
{
    use StepsRunnerTrait;

    public function run()
    {
        // ...
    }

    public function fireEvent($event)
    {
        echo $event;

        return $event;
    }

    public function checkResults($results)
    {
        return is_bool($results) ? $results : true;
    }
}
