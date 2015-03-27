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
use Rocketeer\Services\Ignition\Plugins;
use Symfony\Component\Console\Input\InputArgument;

class PublishCommand extends AbstractCommand
{
    /**
     * The default name.
     *
     * @type string
     */
    protected $name = 'deploy:plugin-config';

    /**
     * The console command description.
     *
     * @type string
     */
    protected $description = 'Publishes the configuration of a plugin';

    /**
     * Whether the command's task should be built
     * into a pipeline or run straight.
     *
     * @type bool
     */
    protected $straight = true;

    /**
     * Run the tasks.
     */
    public function fire()
    {
        $this->laravel->instance('rocketeer.command', $this);

        $publisher = new Plugins($this->laravel);
        $publisher->publish($this->argument('package'));
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
