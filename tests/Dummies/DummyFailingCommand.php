<?php
namespace Rocketeer\Dummies;

use Rocketeer\Abstracts\AbstractCommand;

class DummyFailingCommand extends AbstractCommand
{
    /**
     * @type string
     */
    protected $name = 'nope';

    /**
     * Run the tasks
     *
     * @return void
     */
    public function fire()
    {
        return $this->fireTasksQueue(function() {
           return false;
        });
    }
}
