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
use Rocketeer\Console\TinkerApplication;

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
     * Fire the command
     */
    public function fire()
    {
        $this->prepareEnvironment();

        // Check for Psysh existence
        if (!class_exists('Psy\Shell')) {
            $this->error('Psysh is a required dependency for tinker, run the following command:');
            $this->comment('$ composer require psy/psysh');

            return false;
        }

        $shell = new Shell();
        $shell->setScopeVariables(array(
            'app' => new TinkerApplication($this->laravel),
            'ssh' => $this->bash,
        ));

        return $shell->run();
    }
}
