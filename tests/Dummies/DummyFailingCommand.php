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

use Rocketeer\Abstracts\AbstractCommand;

class DummyFailingCommand extends AbstractCommand
{
    /**
     * @var string
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
