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

class IgniteCommand extends BaseTaskCommand
{
    /**
     * Whether the command's task should be built
     * into a pipeline or run straight.
     *
     * @type boolean
     */
    protected $straight = true;

    /**
     * @return array
     */
    protected function getOptions()
    {
        return array_merge(parent::getOptions(), [
            ['format', 'F', InputOption::VALUE_REQUIRED, 'The format to export the configuration in'],
            ['consolidated', null, InputOption::VALUE_NONE, 'Whether to export the configuration in one file or multiple'],
        ]);
    }
}
