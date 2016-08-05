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
 * Installs one or more plugins.
 */
class Installer extends AbstractTask
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Installs one or more plugins';

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

        if (!$this->files->has($this->paths->getRocketeerPath().'/composer.json')) {
            $this->igniter->exportComposerFile();
        }

        $method = $package ? 'require' : 'install';
        $noDev = $method === 'install' ? '--no-dev' : '--update-no-dev';
        $command = $this->composer()->$method($package, [
            $noDev => '',
            '--working-dir' => $this->paths->getRocketeerPath(),
        ], [
            'COMPOSER_DISCARD_CHANGES' => 1,
        ]);

        // Install plugin
        $this->explainer->line('Installing '.$package);
        $this->run($this->shellCommand($command));
    }
}
