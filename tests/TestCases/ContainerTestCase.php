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
use League\Flysystem\MountManager;
use Mockery;
use PHPUnit_Framework_TestCase;
use Rocketeer\Console\Commands\AbstractCommand;
use Rocketeer\Container;
use Rocketeer\Dummies\Tasks\MyCustomTask;
use Rocketeer\RocketeerServiceProvider;
use Rocketeer\Services\Connections\Connections\Connection;
use Rocketeer\Services\Connections\ConnectionsFactory;
use Rocketeer\TestCases\Modules\Assertions;
use Rocketeer\TestCases\Modules\Building;
use Rocketeer\TestCases\Modules\Contexts;
use Rocketeer\TestCases\Modules\Mocks;
use Rocketeer\Traits\HasLocator;
use Symfony\Component\Console\Output\OutputInterface;

abstract class ContainerTestCase extends PHPUnit_Framework_TestCase
{
    use HasLocator;
    use Mocks;
    use Assertions;
    use Contexts;
    use Building;

    /**
     * @var array
     */
    protected $defaults;

    /**
     * The path to the local fake server.
     *
     * @var string
     */
    protected $server;

    /**
     * @var string
     */
    protected $customConfig;

    /**
     * The path to the local deployments file.
     *
     * @var string
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

        $this->app->add('path.base', '/src');
        $this->app->add('path', $this->app->get('path.base').'/app');
        $this->app->add('path.public', $this->app->get('path.base').'/public');
        $this->app->add('path.storage', $this->app->get('path').'/storage');

        // Create local paths
        $this->home = $_SERVER['HOME'];
        $this->server = realpath(__DIR__.'/../_server').'/foobar';
        $this->customConfig = $this->server.'/.rocketeer';
        $this->deploymentsFile = $this->server.'/deployments.json';

        // Rocketeer classes ------------------------------------------- /

        $serviceProvider = new RocketeerServiceProvider();
        $serviceProvider->setContainer($this->app);
        $serviceProvider->register();

        // Replace some instances with mocks
        $this->app->add('artisan', $this->getArtisan());
        $this->app->add(ConnectionsFactory::class, $this->getConnectionsFactory());
        $this->app->add('rocketeer.command', $this->getCommand());

        $this->app->share('flysystem', function () {
            return new MountManager([
                'local' => $this->files,
                'remote' => $this->files,
            ]);
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
                echo $message;
            }

            return $message;
        };

        $verbose = array_get($options, 'verbose') ? OutputInterface::VERBOSITY_VERY_VERBOSE : OutputInterface::VERBOSITY_NORMAL;
        $command = Mockery::mock(AbstractCommand::class)->shouldIgnoreMissing();
        $command->shouldReceive('getOutput')->andReturn($this->getCommandOutput($verbose));
        $command->shouldReceive('getVerbosity')->andReturn($verbose);

        // Bind the output expectations
        $types = ['comment', 'error', 'line', 'info', 'writeln'];
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
            'option' => false,
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
     * @param string|array $expectations
     *
     * @return Mockery\MockInterface
     */
    protected function getConnectionsFactory($expectations = null)
    {
        return Mockery::mock(ConnectionsFactory::class, [
            'make' => $this->getRemote($expectations),
            'isConnected' => false,
        ]);
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
        $mockedRun = function ($output) {
            return function ($task, $callback) use ($output) {
                $callback($output);
            };
        };

        $run = function ($task, $callback) use ($mockedOutput) {
            if (is_array($task)) {
                $task = implode(' && ', $task);
            } elseif ($task === 'bash --login -c \'echo ROCKETEER\'') {
                $callback('Inappropriate ioctl for device'.PHP_EOL.'ROCKETEER');
            }

            $output = $mockedOutput ? $mockedOutput : shell_exec($task);
            $callback($output);

            return $output;
        };

        $remote = Mockery::mock(Connection::class);
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
            echo $line.PHP_EOL;
        });

        if (is_array($mockedOutput)) {
            $mockedOutput['bash --login -c \'echo ROCKETEER\''] = 'Inappropriate ioctl for device'.PHP_EOL.'ROCKETEER';
            foreach ($mockedOutput as $command => $output) {
                $remote->shouldReceive('run')->with($command, Mockery::any())->andReturnUsing($mockedRun($output));
                $remote->shouldReceive('run')->with([$command], Mockery::any())->andReturnUsing($mockedRun($output));
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
            $defaults[$file] = $this->config->get(''.$file);
        }

        // Build correct keys
        $defaults = array_dot($defaults);
        $keys = array_keys($defaults);
        $keys = array_map(function ($key) {
            return ''.str_replace('config.', null, $key);
        }, $keys);
        $defaults = array_combine($keys, array_values($defaults));

        $overrides = [
            'cache.driver' => 'file',
            'database.default' => 'mysql',
            'default' => 'production',
            'session.driver' => 'file',
            'connections' => [
                'production' => [
                    'host' => '{host}',
                    'username' => '{username}',
                    'password' => '{password}',
                    'root_directory' => dirname($this->server),
                ],
                'staging' => [
                    'host' => '{host}',
                    'username' => '{username}',
                    'password' => '{password}',
                    'root_directory' => dirname($this->server),
                ],
            ],
            'application_name' => 'foobar',
            'logs' => null,
            'remote.permissions.files' => ['tests'],
            'remote.shared' => ['tests/Elements'],
            'remote.keep_releases' => 1,
            'scm' => [
                'branch' => 'master',
                'repository' => 'https://github.com/'.$this->repository,
                'scm' => 'git',
                'shallow' => true,
                'submodules' => true,
            ],
            'strategies.dependencies' => 'Composer',
            'hooks' => [
                'custom' => [MyCustomTask::class],
                'before' => [
                    'deploy' => [
                        'before',
                        'foobar',
                    ],
                ],
                'after' => [
                    'check' => [
                        MyCustomTask::class,
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
