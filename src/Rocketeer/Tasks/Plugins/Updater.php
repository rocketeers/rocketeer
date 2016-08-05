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

namespace Rocketeer\Tasks\Plugins;

use Rocketeer\Tasks\AbstractTask;

/**
 * Updates one or more plugins.
 */
class Updater extends AbstractTask
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Updates one or more plugins';

    /**
     * Whether to run the commands locally
     * or on the server.
     *
     * @var bool
     */
    protected $local = true;

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        // Get package and destination folder
        $package = $this->command->argument('package');

        $arguments = $package ? [$package] : null;
        $options = ['--working-dir' => $this->paths->getRocketeerPath()];
        $env = ['COMPOSER_DISCARD_CHANGES' => 1];

        $command = $this->composer()->update($arguments, $options, $env);
        $this->run($this->shellCommand($command));
    }
}
