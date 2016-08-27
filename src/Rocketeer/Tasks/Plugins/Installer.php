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
        $package = $this->getPackage();
        $method = $package ? 'require' : 'install';

        if (!$this->files->has($this->paths->getRocketeerPath().'/composer.json')) {
            $this->igniter->exportComposerFile();
        }

        // Install plugin
        $this->explainer->line($package ? 'Installing '.$package : 'Setting up Composer');
        $this->runComposerMethod($method, $package);
    }

    /**
     * @param string          $method
     * @param string|string[] $arguments
     */
    protected function runComposerMethod($method, $arguments)
    {
        $noDev = $method === 'install' ? '--no-dev' : '--update-no-dev';
        $options = [$noDev => '', '--working-dir' => $this->paths->getRocketeerPath()];
        $env = ['COMPOSER_DISCARD_CHANGES' => 1];

        // Install plugin
        $command = $this->composer()->$method($arguments, $options, $env);
        $this->run($this->shellCommand($command));
    }

    /**
     * @return array|null|string
     */
    protected function getPackage()
    {
        return array_key_exists('package', $this->command->getInput()->getArguments())
            ? $this->command->argument('package')
            : null;
    }
}
