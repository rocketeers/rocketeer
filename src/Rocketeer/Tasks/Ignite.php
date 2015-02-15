<?php
/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Rocketeer\Tasks;

use Illuminate\Support\Arr;
use Rocketeer\Abstracts\AbstractTask;

/**
 * A task to ignite Rocketeer
 *
 * @author Maxime Fabre <ehtnam6@gmail.com>
 */
class Ignite extends AbstractTask
{
    /**
     * A description of what the task does
     *
     * @type string
     */
    protected $description = "Creates Rocketeer's configuration";

    /**
     * Execute ignite
     */
    public function execute()
    {
        // Export configuration
        $path = ($this->command->getFramework() === 'laravel')
            ? $this->createLaravelConfiguration()
            : $this->createOutsideConfiguration();

        // Replace placeholders
        $parameters = $this->getConfigurationInformations();
        $this->app['rocketeer.igniter']->updateConfiguration($path, $parameters);

        // Display info
        $folder  = basename(dirname($path)).'/'.basename($path);
        $message = '<comment>The Rocketeer configuration was created at</comment> <info>'.$folder.'</info>';

        return $this->explainer->success($message);
    }

    /**
     * Create the configuration outside of Laravel
     *
     * @return string
     */
    protected function createLaravelConfiguration()
    {
        $this->command->call('config:publish', array('package' => 'anahkiasen/rocketeer'));

        return $this->app['path'].'/config/packages/anahkiasen/rocketeer';
    }

    /**
     * Get the configuration stub to use
     *
     * @return string
     */
    protected function createOutsideConfiguration()
    {
        return $this->app['rocketeer.igniter']->exportConfiguration();
    }

    /**
     * Get the core informations to inject in the configuration created
     *
     * @return array
     */
    protected function getConfigurationInformations()
    {
        // Replace credentials
        $repositoryCredentials = $this->credentials->getRepositoryCredentials();
        $name                  = basename($this->paths->getBasePath());

        return array_merge(
            $this->credentials->getServerCredentials(),
            array(
                'connection'       => preg_replace('/#[0-9]+/', null, $this->connections->getCurrent()),
                'scm_repository'   => Arr::get($repositoryCredentials, 'repository'),
                'scm_username'     => Arr::get($repositoryCredentials, 'username'),
                'scm_password'     => Arr::get($repositoryCredentials, 'password'),
                'application_name' => $this->command->ask('What is your application\'s name ? ('.$name.')', $name),
            )
        );
    }
}
