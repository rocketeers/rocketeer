<?php

/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Rocketeer\Dummies\Plugins;

use Rocketeer\Console\Commands\AbstractCommand;

class DummyPluginCommand extends AbstractCommand
{
    protected $name = 'foobar';

    /**
     * Run the tasks.
     */
    public function fire()
    {
        //
    }
}
