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

namespace Rocketeer\Console\Commands\Development;

use Psy\Shell;
use Rocketeer\Console\Commands\AbstractCommand;
use Rocketeer\Console\TinkerApplication;

/**
 * Debug Rocketeer's environment.
 */
class TinkerCommand extends AbstractCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'debug:tinker';

    /**
     * @var string
     */
    protected $description = "Debug Rocketeer's environment";

    /**
     * {@inheritdoc}
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
        $shell->setScopeVariables([
            'app' => new TinkerApplication($this->container),
            'ssh' => $this->bash,
        ]);

        return $shell->run();
    }
}
