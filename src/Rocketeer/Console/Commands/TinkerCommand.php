<?php

/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Rocketeer\Console\Commands;

use Boris\Boris;
use Rocketeer\Abstracts\AbstractCommand;

class TinkerCommand extends AbstractCommand
{
    /**
     * The console command name.
     *
     * @type string
     */
    protected $name = 'tinker';

    /**
     * @type string
     */
    protected $description = "Debug Rocketeer's environment";

    /**
     * Fire the command.
     */
    public function fire()
    {
        $boris = new Boris('rocketeer> ');
        $boris->setLocal([
            'rocketeer' => $this->laravel,
            'ssh'       => $this->laravel['rocketeer.bash'],
        ]);

        $boris->start();
    }
}
