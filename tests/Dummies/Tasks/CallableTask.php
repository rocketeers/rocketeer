<?php
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
