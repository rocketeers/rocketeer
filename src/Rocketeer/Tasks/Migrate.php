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

namespace Rocketeer\Tasks;

use Rocketeer\Interfaces\Strategies\MigrateStrategyInterface;

class Migrate extends AbstractTask
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrates and/or seed the database';

    /**
     * @var MigrateStrategyInterface
     */
    protected $strategy;

    /**
     * @var array
     */
    protected $results = [];

    /**
     * Run the task.
     *
     * @return bool|bool[]
     */
    public function execute()
    {
        // Prepare method
        $this->strategy = $this->getStrategy('Migrate');
        $this->results = [];

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
     * Check if the command can be run.
     *
     * @return bool
     */
    protected function canRunMigrations()
    {
        $connection = $this->connections->getCurrentConnectionKey();
        $hasRole = $connection->getServerCredential('db_role');
        $useRoles = $this->config->getContextually('uses_roles');

        return
            $this->strategy &&
            ($this->getOption('migrate') || $this->getOption('seed')) &&
            (!$useRoles || ($connection->isMultiserver() && $useRoles && $hasRole));
    }

    /**
     * Run a method on the strategy if asked to.
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
