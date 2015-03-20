<?php

/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Rocketeer\TestCases\Modules;

use Mockery\MockInterface;
use Rocketeer\Services\Credentials\Keys\RepositoryKey;

/**
 * @mixin \Rocketeer\TestCases\RocketeerTestCase
 * @author Maxime Fabre <ehtnam6@gmail.com>
 */
trait Contexts
{
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
     * @param array $state
     */
    protected function mockState(array $state)
    {
        $contents = json_encode($state);
        $file     = $this->server.'/state.json';

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
        $this->mock('rocketeer.credentials.handler', 'CredentialsHandler', function (MockInterface $mock) use (
            $credentials
        ) {
            return $mock->shouldReceive('getCurrentRepository')->andReturn(new RepositoryKey($credentials));
        });
    }

    /**
     * Swap the configured connections.
     *
     * @param array $connections
     */
    protected function swapConnections(array $connections)
    {
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
            $this->tasks->plugin('Rocketeer\Plugins\Laravel\LaravelPlugin');
        } else {
            unset($this->app['rocketeer.strategies.framework']);
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
