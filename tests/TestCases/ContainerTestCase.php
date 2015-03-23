<?php

/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Rocketeer\TestCases;

use Closure;
use Illuminate\Container\Container;
use League\Flysystem\MountManager;
use Mockery;
use PHPUnit_Framework_TestCase;
use Rocketeer\RocketeerServiceProvider;
use Rocketeer\Services\Filesystem\GlobPlugin;
use Rocketeer\TestCases\Modules\Assertions;
use Rocketeer\TestCases\Modules\Building;
use Rocketeer\TestCases\Modules\Contexts;
use Rocketeer\TestCases\Modules\Mocks;
use Rocketeer\Traits\HasLocator;

abstract class ContainerTestCase extends PHPUnit_Framework_TestCase
{
    use HasLocator;
    use Mocks;
    use Assertions;
    use Contexts;
    use Building;

    /**
     * @type array
     */
    protected $defaults;

    /**
     * The path to the local fake server.
     *
     * @type string
     */
    protected $server;

    /**
     * @type string
     */
    protected $customConfig;

    /**
     * The path to the local deployments file.
     *
     * @type string
     */
    protected $deploymentsFile;

    /**
     * Override the trait constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Set up the tests.
     */
    public function setUp()
    {
        $this->app = new Container();

        // Paths -------------------------------------------------------- /

        $this->app->instance('path.base', '/src');
        $this->app->instance('path', '/src/app');
        $this->app->instance('path.public', '/src/public');
        $this->app->instance('path.storage', '/src/app/storage');

        // Create local paths
        $this->home            = $_SERVER['HOME'];
        $this->server          = realpath(__DIR__.'/../_server').'/foobar';
        $this->customConfig    = $this->server.'/.rocketeer';
        $this->deploymentsFile = $this->server.'/deployments.json';

        // Replace some instances with mocks
        $this->app['artisan']           = $this->getArtisan();
        $this->app['rocketeer.remote']  = $this->getRemote();
        $this->app['rocketeer.command'] = $this->getCommand();

        // Rocketeer classes ------------------------------------------- /

        $serviceProvider = new RocketeerServiceProvider($this->app);
        $serviceProvider->boot();

        $this->app->singleton('flysystem', function () {
           return new MountManager(['local' => $this->files, 'remote' => $this->files]);
        });

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
     * Mock the Command class.
     *
     * @param array $expectations
     * @param array $options
     * @param bool  $print
     *
     * @return Mockery
     */
    protected function getCommand(array $expectations = [], array $options = [], $print = false)
    {
        $message = function ($message) use ($print) {
            if ($print) {
                print $message;
            }

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
            'argument'        => '',
            'ask'             => '',
            'isInsideLaravel' => false,
            'confirm'         => true,
            'secret'          => '',
            'option'          => false,
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
            $command->shouldReceive('option')->withNoArgs()->andReturn($options);
            foreach ($options as $key => $value) {
                $command->shouldReceive('option')->with($key)->andReturn($value);
            }
        }

        return $command;
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

            return $output;
        };

        $remote = Mockery::mock('Rocketeer\Services\Connections\Connections\Connection');
        $remote->shouldReceive('connected')->andReturn(true);
        $remote->shouldReceive('into')->andReturn(Mockery::self());
        $remote->shouldReceive('status')->andReturn(0)->byDefault();
        $remote->shouldReceive('runRaw')->andReturnUsing($run)->byDefault();
        $remote->shouldReceive('connected')->andReturn(true);
        $remote->shouldReceive('connection')->andReturnSelf();
        $remote->shouldReceive('isCompatibleWith')->andReturn(true);
        $remote->shouldReceive('getUsername')->andReturn('anahkiasen');
        $remote->shouldReceive('getString')->andReturnUsing(function ($file) {
            return $this->files->read($file);
        });
        $remote->shouldReceive('putString')->andReturnUsing(function ($file, $contents) {
            return $this->files->upsert($file, $contents);
        });
        $remote->shouldReceive('display')->andReturnUsing(function ($line) {
            print $line.PHP_EOL;
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
        $files    = ['config', 'hooks', 'paths', 'remote', 'scm', 'stages', 'strategies'];
        foreach ($files as $file) {
            $defaults[$file] = $this->config->get(''.$file);
        }

        // Build correct keys
        $defaults = array_dot($defaults);
        $keys     = array_keys($defaults);
        $keys     = array_map(function ($key) {
            return ''.str_replace('config.', null, $key);
        }, $keys);
        $defaults = array_combine($keys, array_values($defaults));

        $overrides = [
            'cache.driver'             => 'file',
            'database.default'         => 'mysql',
            'default'                  => 'production',
            'session.driver'           => 'file',
            'connections'              => [
                'production' => ['host' => '{host}', 'username' => '{username}', 'password' => '{password}'],
                'staging'    => ['host' => '{host}', 'username' => '{username}', 'password' => '{password}'],
            ],
            'application_name'         => 'foobar',
            'logs'                     => null,
            'remote.permissions.files' => ['tests'],
            'remote.shared'            => ['tests/Elements'],
            'remote.keep_releases'     => 1,
            'remote.root_directory'    => dirname($this->server),
            'scm'                      => [
                'branch'     => 'master',
                'repository' => 'https://github.com/'.$this->repository,
                'scm'        => 'git',
                'shallow'    => true,
                'submodules' => true,
            ],
            'strategies.dependencies'  => 'Composer',
            'hooks'                    => [
                'custom' => ['Rocketeer\Dummies\Tasks\MyCustomTask'],
                'before' => [
                    'deploy' => [
                        'before',
                        'foobar',
                    ],
                ],
                'after'  => [
                    'check'  => [
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
     * @param int $verbosity
     *
     * @return Mockery\Mock
     */
    protected function getCommandOutput($verbosity = 0)
    {
        $output = Mockery::mock('Symfony\Component\Console\Output\OutputInterface')->shouldIgnoreMissing();
        $output->shouldReceive('getVerbosity')->andReturn($verbosity);

        return $output;
    }
}
