<?php

/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Rocketeer\Console\Commands\Plugins;

use Rocketeer\Abstracts\AbstractCommand;
use Symfony\Component\Console\Input\InputArgument;

class InstallCommand extends AbstractCommand
{
    /**
     * The default name.
     *
     * @type string
     */
    protected $name = 'deploy:plugin-install';

    /**
     * The console command description.
     *
     * @type string
     */
    protected $description = 'Install a plugin';

    /**
     * Whether the command's task should be built
     * into a pipeline or run straight.
     *
     * @type bool
     */
    protected $straight = true;

    /**
     * Run the tasks.
     *
     * @return int
     */
    public function fire()
    {
        return $this->fireTasksQueue('Plugins\Installer');
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
