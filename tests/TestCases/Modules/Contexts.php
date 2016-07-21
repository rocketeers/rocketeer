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

use Rocketeer\Plugins\Laravel\Laravel;
use Symfony\Component\Finder\Finder;

/**
 * @mixin \Rocketeer\TestCases\RocketeerTestCase
 *
 * @author Maxime Fabre <ehtnam6@gmail.com>
 */
trait Contexts
{
    /**
     * @param string $path
     */
    protected function mockBasepath($path)
    {
        $this->container->add('path.base', $path);
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
     *
     * @internal param array $expectations
     */
    protected function pretend($options = [])
    {
        $options = array_merge(['--pretend' => true], (array) $options);

        $this->mockCommand($options);
    }

    ////////////////////////////////////////////////////////////////////////////////
    ///////////////////////////////// CONFIGURATION ////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////

    /**
     * Swap the current config.
     *
     * @param array $config
     */
    protected function swapConfig($config = [])
    {
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
        $this->mockConfig([
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

    /**
     * Replicates the configuration onto the VFS.
     */
    protected function replicateConfiguration()
    {
        $folder = $this->configurationLoader->getFolders()[0];

        $this->replicateFolder($folder);
        $this->replicateFolder(__DIR__.'/../../../src/stubs');

        $this->configurationLoader->setFolders([$folder]);
        $this->configurationLoader->getCache()->flush();

        return $folder;
    }

    protected function replicateFolder($folder)
    {
        $folder = realpath($folder);

        $this->files->createDir($folder);
        $files = (new Finder())->in($folder)->files();
        foreach ($files as $file) {
            $contents = file_get_contents($file->getPathname());
            $this->files->write($file->getPathname(), $contents);
        }
    }

    //////////////////////////////////////////////////////////////////////
    ///////////////////////////// CREDENTIALS ////////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * @param array $credentials
     */
    protected function swapRepositoryCredentials(array $credentials)
    {
        $scm = $this->config->get('scm');
        $this->config->set('scm', array_merge($scm, $credentials));

        $this->localStorage->destroy();
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

        $this->mockConfig([
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
            $this->container->addServiceProvider(Laravel::class);
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
