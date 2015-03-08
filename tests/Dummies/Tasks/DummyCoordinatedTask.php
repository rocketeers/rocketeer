<?php
namespace Rocketeer\Dummies\Tasks;

use Rocketeer\Abstracts\AbstractTask;

class DummyCoordinatedTask extends AbstractTask
{
    /**
     * Run the task.
     *
     * @return string
     */
    public function execute()
    {
        echo 'A:'.$this->connections->getCurrentConnection().PHP_EOL;

        $this->coordinator->whenAllServersReadyTo('rumble', function () {
            echo 'B:'.$this->connections->getCurrentConnection().PHP_EOL;

            $this->coordinator->whenAllServersReadyTo('tumble', function () {
                echo 'C:'.$this->connections->getCurrentConnection().PHP_EOL;
            });
        });
    }
}
