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

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * Rollback to the previous release, or to a specific one.
 *
 * @author Maxime Fabre <ehtnam6@gmail.com>
 */
class RollbackCommand extends BaseTaskCommand
{
    /**
     * Get the console command arguments.
     *
     * @return string[][]
     */
    protected function getArguments()
    {
        return [
            ['release', InputArgument::OPTIONAL, 'The release to rollback to'],
        ];
    }

    /**
     * Get the console command options.
     *
     * @return array<string[]|array<string|null>>
     */
    protected function getOptions()
    {
        return array_merge(parent::getOptions(), [
            ['list', 'L', InputOption::VALUE_NONE, 'Shows the available releases to rollback to'],
        ]);
    }
}
