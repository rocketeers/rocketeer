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

use Rocketeer\Tasks\AbstractTask;

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
