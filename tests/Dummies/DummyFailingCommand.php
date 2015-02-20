<?php
namespace Rocketeer\Dummies;

use Rocketeer\Abstracts\Commands\AbstractCommand;

class DummyFailingCommand extends AbstractCommand
{
    /**
     * @type string
     */
    protected $name = 'nope';

    /**
     * Run the tasks.
     */
    public function fire()
    {
        return $this->fireTasksQueue(function () {
            return false;
        });
    }
}
