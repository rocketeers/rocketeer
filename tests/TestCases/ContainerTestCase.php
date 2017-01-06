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

namespace Rocketeer\TestCases;

use Closure;
use Illuminate\Container\Container;
use Illuminate\Filesystem\Filesystem;
use Mockery;
use PHPUnit_Framework_TestCase;
use Rocketeer\RocketeerServiceProvider;
use Rocketeer\Traits\HasLocator;

abstract class ContainerTestCase extends PHPUnit_Framework_TestCase
{
    use HasLocator;

    /**
     * @var arra
     */
    protected $defaults;

    /**
     * Override the trait constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * The test repository.
     *
     * @var string
     */
    protected $repository = 'Anahkiasen/html-object.git';

    /**
     * Set up the tests.
     */
    public function setUp()
    {
        $this->app = new Container();

        // Laravel classes --------------------------------------------- /

        $this->app->instance('path.base', '/src');
        $this->app->instance('path', '/src/app');
        $this->app->instance('path.public', '/src/public');
        $this->app->instance('path.storage', '/src/app/storage');

        $this->app['files'] = new Filesystem();
        $this->app['artisan'] = $this->getArtisan();
        $this->app['rocketeer.remote'] = $this->getRemote();
        $this->app['rocketeer.command'] = $this->getCommand();

        // Rocketeer classes ------------------------------------------- /

        $serviceProvider = new RocketeerServiceProvider($this->app);
        $serviceProvider->boot();

        // Swap some instances with Mockeries -------------------------- /

        $this->swapConfig();
    }

    /**
     * Tears down the tests.
     */
    public function tearDown()
    {
        Mockery::close();
    }

    ////////////////////////////////////////////////////////////////////
    ///////////////////////// MOCKED INSTANCES /////////////////////////
    ////////////////////////////////////////////////////////////////////

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

        $this->app[$handle] = $mockery;

        return $mockery;
    }

    /**
     * Mock the Command class.
     *
     * @param array $expectations
     * @param array $options
     *
     * @return Mockery
     */
    protected function getCommand(array $expectations = [], array $options = [])
    {
        $message = function ($message) {
            return $message;
        };

        $verbose = array_get($options, 'verbose') ? 1 : 0;
        $command = Mockery::mock('Command')->shouldIgnoreMissing();
        $command->shouldReceive('getOutput')->andReturn($this->getCommandOutput($verbose));

        // Bind the output expectations
        $types = ['comment', 'error', 'line', 'info'];
        foreach ($types as $type) {
            if (!array_key_exists($type, $expectations)) {
                $command->shouldReceive($type)->andReturnUsing($message);
            }
        }

        // Merge defaults
        $expectations = array_merge([
            'argument' => '',
            'ask' => '',
            'isInsideLaravel' => false,
            'confirm' => true,
            'secret' => '',
            'option' => null,
        ], $expectations);

        // Bind expecations
        foreach ($expectations as $key => $value) {
            if ($key === 'option') {
                $command->shouldReceive($key)->andReturn($value)->byDefault();
            } else {
                $returnMethod = $value instanceof Closure ? 'andReturnUsing' : 'andReturn';
                $command->shouldReceive($key)->$returnMethod($value);
            }
        }

        // Bind options
        if ($options) {
            foreach ($options as $key => $value) {
                $command->shouldReceive('option')->with($key)->andReturn($value);
            }
        }

        return $command;
    }

    /**
     * Mock the Config component.
     *
     * @param array $expectations
     *
     * @return Mockery
     */
    protected function getConfig($expectations = [])
    {
        $config = Mockery::mock('Illuminate\Config\Repository');
        $config->shouldIgnoreMissing();

        $defaults = $this->getFactoryConfiguration();
        $expectations = array_merge($defaults, $expectations);
        foreach ($expectations as $key => $value) {
            $config->shouldReceive('get')->with($key)->andReturn($value);
        }

        return $config;
    }

    /**
     * Swap the current config.
     *
     * @param array $config
     */
    protected function swapConfig($config = [])
    {
        $this->connections->disconnect();
        $this->app['config'] = $this->getConfig($config);
        $this->tasks->registerConfiguredEvents();
    }

    /**
     * Mock the Remote component.
     *
     * @param string|array|null $mockedOutput
     *
     * @return Mockery
     */
    protected function getRemote($mockedOutput = null)
    {
        $run = function ($task, $callback) use ($mockedOutput) {
            if (is_array($task)) {
                $task = implode(' && ', $task);
            }

            $output = $mockedOutput ? $mockedOutput : shell_exec($task);
            $callback($output);
        };

        $remote = Mockery::mock('Illuminate\Remote\Connection');
        $remote->shouldReceive('connected')->andReturn(true);
        $remote->shouldReceive('into')->andReturn(Mockery::self());
        $remote->shouldReceive('status')->andReturn(0)->byDefault();
        $remote->shouldReceive('runRaw')->andReturnUsing($run)->byDefault();
        $remote->shouldReceive('getString')->andReturnUsing(function ($file) {
            return file_get_contents($file);
        });
        $remote->shouldReceive('putString')->andReturnUsing(function ($file, $contents) {
            return file_put_contents($file, $contents);
        });
        $remote->shouldReceive('display')->andReturnUsing(function ($line) {
            echo $line.PHP_EOL;
        });

        if (is_array($mockedOutput)) {
            foreach ($mockedOutput as $command => $output) {
                $remote->shouldReceive('run')->with($command)->andReturn($output);
            }
        } else {
            $remote->shouldReceive('run')->andReturnUsing($run)->byDefault();
        }

        return $remote;
    }

    /**
     * Mock Artisan.
     *
     * @return Mockery
     */
    protected function getArtisan()
    {
        $artisan = Mockery::mock('Artisan');
        $artisan->shouldReceive('add')->andReturnUsing(function ($command) {
            return $command;
        });

        return $artisan;
    }

    /**
     * @return array
     */
    protected function getFactoryConfiguration()
    {
        if ($this->defaults) {
            return $this->defaults;
        }

        // Base the mocked configuration off the factory values
        $defaults = [];
        $files = ['config', 'hooks', 'paths', 'remote', 'scm', 'stages', 'strategies'];
        foreach ($files as $file) {
            $defaults[$file] = $this->config->get('rocketeer::'.$file);
        }

        // Build correct keys
        $defaults = array_dot($defaults);
        $keys = array_keys($defaults);
        $keys = array_map(function ($key) {
            return 'rocketeer::'.str_replace('config.', null, $key);
        }, $keys);
        $defaults = array_combine($keys, array_values($defaults));

        $overrides = [
            'cache.driver' => 'file',
            'database.default' => 'mysql',
            'remote.default' => 'production',
            'session.driver' => 'file',
            'remote.connections' => [
                'production' => [],
                'staging' => [],
            ],
            'rocketeer::paths.git' => 'git',
            'rocketeer::paths.svn' => 'svn',
            'rocketeer::application_name' => 'foobar',
            'rocketeer::logs' => null,
            'rocketeer::remote.permissions.files' => ['tests'],
            'rocketeer::remote.shared' => ['tests/Elements'],
            'rocketeer::remote.keep_releases' => 1,
            'rocketeer::remote.root_directory' => __DIR__.'/../_server/',
            'rocketeer::scm' => [
                'branch' => 'master',
                'repository' => 'https://github.com/'.$this->repository,
                'scm' => 'git',
                'shallow' => true,
                'submodules' => true,
            ],
            'rocketeer::strategies.dependencies' => 'Composer',
            'rocketeer::hooks.custom' => ['Rocketeer\Dummies\Tasks\MyCustomTask'],
            'rocketeer::hooks' => [
                'before' => [
                    'deploy' => [
                        'before',
                        'foobar',
                    ],
                ],
                'after' => [
                    'check' => [
                        'Rocketeer\Dummies\Tasks\MyCustomTask',
                    ],
                    'deploy' => [
                        'after',
                        'foobar',
                    ],
                ],
            ],
        ];

        // Assign options to expectations
        $this->defaults = array_merge($defaults, $overrides);

        return $this->defaults;
    }

    /**
     * @return Mockery\Mock
     */
    protected function getCommandOutput($verbosity = 0)
    {
        $output = Mockery::mock('OutputInterface')->shouldIgnoreMissing();
        $output->shouldReceive('getVerbosity')->andReturn($verbosity);

        return $output;
    }
}
