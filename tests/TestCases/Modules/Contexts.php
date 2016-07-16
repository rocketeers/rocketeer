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

namespace Rocketeer\TestCases\Modules;

use Rocketeer\Plugins\Laravel\LaravelPlugin;
use Rocketeer\Services\Connections\Credentials\CredentialsHandler;
use Rocketeer\Services\Connections\Credentials\Keys\RepositoryKey;

/**
 * @mixin \Rocketeer\TestCases\RocketeerTestCase
 *
 * @author Maxime Fabre <ehtnam6@gmail.com>
 */
trait Contexts
{
    /**
     * @param string $path
     * @param string $directorySeparator
     */
    protected function mockBasepath($path, $directorySeparator = DIRECTORY_SEPARATOR)
    {
        $this->container->add('path.base', $path);
        $this->container->add('path.rocketeer.config', $path.$directorySeparator.'.rocketeer');
    }

    /**
     * @param string|null $system
     */
    protected function mockOperatingSystem($system = null)
    {
        $system = $system ?: PHP_OS;

        $this->localStorage->set('production.os', $system);
        $this->localStorage->set('staging.os', $system);
    }

    /**
     * @param bool  $usesHhvm
     * @param array $additional
     */
    protected function mockHhvm($usesHhvm = true, array $additional = [])
    {
        $this->mockRemote(array_merge([
            'which php' => 'php',
            'php -r "print defined(\'HHVM_VERSION\');"' => (int) $usesHhvm,
        ], $additional));
    }

    /**
     * @param array $state
     */
    protected function mockState(array $state)
    {
        $contents = json_encode($state);
        $file = $this->server.'/state.json';

        $this->files->upsert($file, $contents);
    }

    /**
     * Set Rocketeer in pretend mode.
     *
     * @param array $options
     * @param array $expectations
     */
    protected function pretend($options = [], $expectations = [])
    {
        $options['pretend'] = true;

        $this->mockCommand($options, $expectations);
    }

    /**
     * Swap the current config.
     *
     * @param array $config
     */
    protected function swapConfig($config = [])
    {
        $this->connections->disconnect();
        $this->mockConfig($config);
        $this->tasks->registerConfiguredEvents();
    }

    /**
     * Make the config return specific SCM config.
     *
     * @param string      $repository
     * @param string|null $username
     * @param string|null $password
     */
    protected function expectRepositoryConfig($repository, $username = null, $password = null)
    {
        $this->swapConfig([
            'scm.repository' => $repository,
            'scm.username' => $username,
            'scm.password' => $password,
        ]);
    }

    /**
     * Disable the test events.
     */
    protected function disableTestEvents()
    {
        $this->swapConfig([
            'hooks' => [],
        ]);
    }

    //////////////////////////////////////////////////////////////////////
    ///////////////////////////// CREDENTIALS ////////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * @param array $credentials
     */
    protected function swapRepositoryCredentials(array $credentials)
    {
        /** @var CredentialsHandler $prophecy */
        $prophecy = $this->bindProphecy(CredentialsHandler::class);
        $prophecy->getCurrentRepository()->willReturn(new RepositoryKey($credentials));
    }

    /**
     * Swap the configured connections.
     *
     * @param array $connections
     */
    protected function swapConnections(array $connections)
    {
        // Merge defaults to connections
        foreach ($connections as $key => $connection) {
            $connections[$key] = array_merge([
                'root_directory' => dirname($this->server),
            ], $connection);
        }

        $this->swapConfig([
            'connections' => $connections,
        ]);
    }

    //////////////////////////////////////////////////////////////////////
    ////////////////////////// PACKAGE MANAGERS //////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * Mock usage of Laravel framework.
     *
     * @param bool $uses
     */
    protected function usesLaravel($uses = true)
    {
        if ($uses) {
            $this->tasks->plugin(LaravelPlugin::class);
        } else {
            $this->container->remove('rocketeer.strategies.framework');
        }
    }

    /**
     * Mock the Composer check.
     *
     * @param bool        $uses
     * @param string|null $stage
     */
    protected function usesComposer($uses = true, $stage = null)
    {
        $this->mockPackageManagerUsage($uses, 'composer.json', $stage, '{}');
    }

    /**
     * Mock the Bundler check.
     *
     * @param bool        $uses
     * @param string|null $stage
     */
    protected function usesBundler($uses = true, $stage = null)
    {
        $this->mockPackageManagerUsage($uses, 'Gemfile', $stage);
    }

    /**
     * Mock the use of a package manager.
     *
     * @param bool        $uses
     * @param string      $filename
     * @param string|null $stage
     * @param string|null $contents
     */
    protected function mockPackageManagerUsage($uses, $filename, $stage = null, $contents = null)
    {
        $manifest = $this->server.'/';
        $manifest .= $stage ? $stage.'/' : null;
        $manifest .= 'releases/20000000000000/'.$filename;

        // Create directory if necessary
        $folder = dirname($manifest);
        if (!$this->files->isDirectory($folder)) {
            $this->files->createDir($folder);
        }

        if ($uses) {
            $this->files->put($manifest, $contents);
        } elseif ($this->files->has($manifest)) {
            $this->files->delete($manifest);
        }
    }
}
