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

use Symfony\Component\Console\Input\InputOption;

/**
 * Update the remote server without doing a new release.
 *
 * @author Maxime Fabre <ehtnam6@gmail.com>
 */
class UpdateCommand extends BaseTaskCommand
{
    /**
     * Get the console command options.
     *
     * @return array<string[]|array<string|null>>
     */
    protected function getOptions()
    {
        return array_merge(parent::getOptions(), [
            ['migrate', 'm', InputOption::VALUE_NONE, 'Run the migrations'],
            ['seed', 's', InputOption::VALUE_NONE, 'Seed the database after migrating the database'],
            ['no-clear', null, InputOption::VALUE_NONE, "Don't clear the application's cache after the update"],
        ]);
    }
}
