<?php
/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Rocketeer\Console\Commands\Development;

use Psy\Shell;
use Rocketeer\Abstracts\AbstractCommand;

class TinkerCommand extends AbstractCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'tinker';

    /**
     * @type string
     */
    protected $description = "Debug Rocketeer's environment";

    /**
     * Fire the command
     */
    public function fire()
    {
        $shell = new Shell();
        $shell->setScopeVariables(array(
            'rocketeer' => $this->laravel,
            'ssh'       => $this->laravel['rocketeer.bash'],
        ));

        return $shell->run();
    }
}
