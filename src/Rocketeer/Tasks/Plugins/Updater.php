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
        $command = $this->composer()->update($arguments, [
            '--working-dir' => $this->paths->getRocketeerPath(),
        ]);

        $this->run($this->shellCommand($command));
    }
}
