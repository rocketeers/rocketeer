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

use Rocketeer\Strategies\Migrate\MigrateStrategyInterface;

/**
 * Migrates and/or seed the database.
 */
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
     * @var array
     */
    protected $roles = ['db'];

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        // Prepare method
        $this->strategy = $this->getStrategy('Migrate');
        $this->results = [];

        // Cancel if nothing to run
        if (!$this->strategy) {
            $this->explainer->line('No strategy configured to run migrations');

            return true;
        }

        // Migrate the database
        $this->runStrategyCommand('migrate', 'Running outstanding migrations');
        $this->runStrategyCommand('seed', 'Seeding database');

        return $this->results;
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
