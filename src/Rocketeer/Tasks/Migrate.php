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

class Migrate extends AbstractTask
{
    /**
     * The console command description.
     *
     * @type string
     */
    protected $description = 'Migrates and/or seed the database';

    /**
     * Run the task.
     *
     * @return bool|boolean[]
     */
    public function execute()
    {
        $results = [];

        // Get strategy and options
        $migrate  = $this->getOption('migrate');
        $seed     = $this->getOption('seed');
        $strategy = $this->getStrategy('Migrate');

        /*
         * For Multi-Server environments, usually the migrations need to be run in
         * one server only. For that reason I use a 'role' array in the connection (or servers) array
         * to show in which server the migration should be run.
         * iI it's NOT a multiserver connection, then proceed as usual.
         */

        $serverCredentials = $this->connections->getServerCredentials();
        $multiserver       = $this->connections->isMultiserver($this->connections->getConnection());
        $hasRole           = (isset($serverCredentials['db_role']) && $serverCredentials['db_role']);
        $useRoles          = $this->config->get('rocketeer::use_roles');

        // Cancel if nothing to run
        if ($strategy === false || ($migrate === false && $seed === false) || ($useRoles === true && $multiserver === true && $hasRole === false)) {
            $this->explainer->line('No outstanding migrations or server not assigned db role');

            return true;
        }

        // Migrate the database
        if ($migrate === true) {
            $this->explainer->line('Running outstanding migrations');
            $results[] = $strategy->migrate();
        }

        // Seed it
        if ($seed === true) {
            $this->explainer->line('Seeding database');
            $results[] = $strategy->seed();
        }

        return $results;
    }
}
