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
 * Runs the Deploy task and then cleans up deprecated releases.
 *
 * @author Maxime Fabre <ehtnam6@gmail.com>
 */
class DeployCommand extends BaseTaskCommand
{
    /**
     * The default name.
     *
     * @type string
     */
    protected $name = 'deploy:deploy';

    /**
     * Execute the tasks.
     *
     * @return int
     */
    public function fire()
    {
        return $this->fireTasksQueue([
            'deploy',
            'cleanup',
        ]);
    }

    /**
     * Get the console command options.
     *
     * @return array<string[]|array<string|null>>
     */
    protected function getOptions()
    {
        return array_merge(parent::getOptions(), [
            ['tests', 't', InputOption::VALUE_NONE, 'Runs the tests on deploy'],
            ['migrate', 'm', InputOption::VALUE_NONE, 'Run the migrations'],
            ['seed', 's', InputOption::VALUE_NONE, 'Seed the database (after migrating it if --migrate)'],
            ['clean-all', null, InputOption::VALUE_NONE, 'Cleanup all but the current release on deploy'],
        ]);
    }
}
