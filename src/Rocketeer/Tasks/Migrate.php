<?php

/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Rocketeer\Tasks;

use Rocketeer\Abstracts\AbstractTask;
use Rocketeer\Interfaces\Strategies\MigrateStrategyInterface;

/*
 * For Multi-Server environments, usually the migrations need to be run in
 * one server only. For that reason I use a 'role' array in the connection (or servers) array
 * to show in which server the migration should be run.
 * If it's NOT a multiserver connection, then proceed as usual.
 */

class Migrate extends AbstractTask
{
    /**
     * The console command description.
     *
     * @type string
     */
    protected $description = 'Migrates and/or seed the database';

    /**
     * @type MigrateStrategyInterface
     */
    protected $strategy;

    /**
     * @type array
     */
    protected $results = [];

    /**
     * Run the task
     *
     * @return boolean|boolean[]
     */
    public function execute()
    {
        // Prepare method
        $this->strategy = $this->getStrategy('Migrate');
        $this->results  = [];

        // Cancel if nothing to run
        if (!$this->canRunMigrations()) {
            $this->explainer->line('No outstanding migrations or server not assigned db role');

            return true;
        }

        // Migrate the database
        $this->runStrategyCommand('migrate', 'Running outstanding migrations');
        $this->runStrategyCommand('seed', 'Seeding database');

        return $this->results;
    }

    /**
     * Check if the command can be run
     *
     * @return boolean
     */
    protected function canRunMigrations()
    {
        $connection = $this->connections->getCurrentConnection();
        $hasRole    = array_get($connection->getServerCredentials(), 'db_role');
        $useRoles   = $this->rocketeer->getOption('uses_roles');

        return
            $this->strategy &&
            ($this->getOption('migrate') || $this->getOption('seed')) &&
            (!$useRoles || ($connection->multiserver && $useRoles && $hasRole));
    }

    /**
     * Run a method on the strategy if asked to
     *
     * @param string $method
     * @param string $message
     */
    protected function runStrategyCommand($method, $message)
    {
        if (!$this->getOption($method)) {
            return;
        }

        $this->explainer->line($message);
        $this->results[] = $this->strategy->$method();
    }
}
