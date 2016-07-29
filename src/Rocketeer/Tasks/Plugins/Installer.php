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

class Installer extends AbstractTask
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Installs plugins';

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
        $folder = $this->paths->getRocketeerPath();

        // Create composer manifest if it does not exist
        $manifest = $folder.'/composer.json';
        if (!$this->files->has($manifest)) {
            $contents = json_encode(['minimum-stability' => 'dev', 'prefer-stable' => true], JSON_PRETTY_PRINT);
            $this->files->put($manifest, $contents);
        }

        $method = $package ? 'require' : 'install';
        $command = $this->composer()->$method($package, [
            '--working-dir' => $folder,
        ]);

        // Install plugin
        $this->explainer->line('Installing '.$package);
        $this->run($this->shellCommand($command));

        // Prune duplicate Rocketeer
        $this->files->deleteDir($folder.'/vendor/anahkiasen/rocketeer');
    }
}
