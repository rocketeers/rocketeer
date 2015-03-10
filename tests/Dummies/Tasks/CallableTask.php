<?php

/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Rocketeer\Dummies\Tasks;

use Rocketeer\Tasks\Closure;

class CallableTask
{
    /**
     * @param Closure $task
     *
     * @return string
     */
    public function someMethod(Closure $task)
    {
        return get_class($task);
    }

    public function fire()
    {
        echo 'FIRED';
    }
}
