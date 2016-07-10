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

namespace Rocketeer\Console\Commands;

use Symfony\Component\Console\Input\InputArgument;

class AbstractPluginCommand extends AbstractCommand
{
    /**
     * The plugin task to fire.
     *
     * @var string
     */
    protected $pluginTask;

    /**
     * Whether the command's task should be built
     * into a pipeline or run straight.
     *
     * @var bool
     */
    protected $straight = true;

    /**
     * {@inheritdoc}
     */
    public function fire()
    {
        return $this->fireTasksQueue('Plugins\\'.$this->pluginTask);
    }

    /**
     * Get the console command arguments.
     *
     * @return string[][]
     */
    protected function getArguments()
    {
        return [
            ['package', InputArgument::REQUIRED, 'The package to publish the configuration for'],
        ];
    }
}
