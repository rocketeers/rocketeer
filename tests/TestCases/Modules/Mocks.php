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

use Closure;
use League\Flysystem\Filesystem;
use Mockery;
use Mockery\MockInterface;

/**
 * @mixin \Rocketeer\TestCases\RocketeerTestCase
 *
 * @author Maxime Fabre <ehtnam6@gmail.com>
 */
trait Mocks
{
    /**
     * Bind a mocked instance in the Container.
     *
     * @param string  $handle
     * @param string  $class
     * @param Closure $expectations
     * @param bool    $partial
     *
     * @return Mockery
     */
    protected function mock($handle, $class = null, Closure $expectations = null, $partial = true)
    {
        $class = $class ?: $handle;
        $mockery = Mockery::mock($class);
        if ($partial) {
            $mockery = $mockery->shouldIgnoreMissing();
        }

        if ($expectations) {
            $mockery = $expectations($mockery)->mock();
        }

        $this->app->add($handle, $mockery);

        return $mockery;
    }

    /**
     * Mock the ReleasesManager.
     *
     * @param Closure $expectations
     *
     * @return Mockery
     */
    protected function mockReleases(Closure $expectations)
    {
        return $this->mock('rocketeer.releases', 'ReleasesManager', $expectations);
    }

    /**
     * Mock a Command.
     *
     * @param array $options
     * @param array $expectations
     * @param bool  $print
     */
    protected function mockCommand($options = [], $expectations = [], $print = false)
    {
        // Default options
        $options = array_merge([
            'pretend' => false,
            'verbose' => false,
            'tests' => false,
            'migrate' => false,
            'seed' => false,
            'stage' => false,
            'parallel' => false,
            'update' => false,
        ], $options);

        $this->app->add('rocketeer.command', $this->getCommand($expectations, $options, $print));
    }

    /**
     * Mock a command that echoes out its output.
     */
    protected function mockEchoingCommand()
    {
        $this->mock('rocketeer.command', 'Command', function (MockInterface $mock) {
            return $mock->shouldReceive('writeln')->andReturnUsing(function ($input) {
                echo $input;
            });
        });
    }

    /**
     * Mock the RemoteHandler.
     *
     * @param string|array|null $expectations
     */
    protected function mockRemote($expectations = null)
    {
        $this->app->add('remote', $this->getRemote($expectations));
    }

    /**
     * @param Closure|null $expectations
     */
    protected function mockFiles(Closure $expectations = null)
    {
        $this->mock('files', Filesystem::class, $expectations);
    }

    /**
     * @param array $expectations
     */
    public function mockConfig(array $expectations)
    {
        $defaults = $this->getFactoryConfiguration();
        $defaults = array_merge($defaults, [
                'remote.shell' => false,
                'paths.app' => $this->app->get('path.base'),
            ]
        );

        // Set core expectations
        foreach ($defaults as $key => $value) {
            $this->config->set($key, $value);
        }

        // Set additional expectations
        foreach ($expectations as $key => $value) {
            $this->config->set($key, $value);
        }
    }
}
