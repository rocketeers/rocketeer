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

/**
 * Rollback to the previous release, or to a specific one.
 */
class Rollback extends AbstractTask
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Rollback to the previous release, or to a specific one';

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        return $this->executeStrategyMethod('Rollback', 'rollback') ?: $this->halt();
    }
}
