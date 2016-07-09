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
use League\Flysystem\Filesystem;
use League\Flysystem\MountManager;
use League\Flysystem\Vfs\VfsAdapter;
use Mockery;
use Mockery\MockInterface;
use PHPUnit_Framework_TestCase;
use Prophecy\Argument;
use Rocketeer\Console\Commands\AbstractCommand;
use Rocketeer\Container;
use Rocketeer\Dummies\Tasks\MyCustomTask;
use Rocketeer\Services\Connections\Connections\Connection;
use Rocketeer\Services\Connections\ConnectionsFactory;
use Rocketeer\Services\Connections\Credentials\Keys\ConnectionKey;
use Rocketeer\Services\Filesystem\Plugins\IncludePlugin;
use Rocketeer\Services\Filesystem\Plugins\IsDirectoryPlugin;
use Rocketeer\Services\Filesystem\Plugins\RequirePlugin;
use Rocketeer\Services\Filesystem\Plugins\UpsertPlugin;
use Rocketeer\TestCases\Modules\Assertions;
use Rocketeer\TestCases\Modules\Building;
use Rocketeer\TestCases\Modules\Contexts;
use Rocketeer\TestCases\Modules\Mocks;
use Rocketeer\Traits\ContainerAwareTrait;
use Symfony\Component\Console\Output\OutputInterface;
use VirtualFileSystem\FileSystem as Vfs;

abstract class ContainerTestCase extends PHPUnit_Framework_TestCase
{
    use Mocks;
    use Assertions;
    use Contexts;
    use Building;
    use ContainerAwareTrait;

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
     * @var string
     */
    protected $home;

    /**
     * Set up the tests.
     */
    public function setUp()
    {
        $this->container = new Container();

        // Create local paths
        $this->home = $_SERVER['HOME'];
        $this->server = realpath(__DIR__.'/../_server').'/foobar';
        $this->customConfig = $this->server.'/.rocketeer';
        $this->deploymentsFile = $this->server.'/deployments.json';

        // Rocketeer classes ------------------------------------------- /

        $this->container = new Container();

        // Paths -------------------------------------------------------- /

        $this->container->add('path.base', '/src');
        $this->container->add('path', $this->container->get('path.base').'/app');
        $this->container->add('path.public', $this->container->get('path.base').'/public');
        $this->container->add('path.storage', $this->container->get('path').'/storage');

        // Replace some instances with mocks
        $this->container->add('artisan', $this->getArtisan());
        $this->container->add(ConnectionsFactory::class, $this->getConnectionsFactory());
        $this->container->add('rocketeer.command', $this->getCommand());

        $filesystem = new Filesystem(new VfsAdapter(new Vfs()));
        $filesystem->addPlugin(new IsDirectoryPlugin());
        $filesystem->addPlugin(new UpsertPlugin());
        $filesystem->addPlugin(new IncludePlugin());
        $filesystem->addPlugin(new RequirePlugin());

        $this->container->add('files', $filesystem);

        $this->container->share('flysystem', function () use ($filesystem) {
            return new MountManager([
                'local' => $filesystem,
                'remote' => $filesystem,
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

        $verbose = array_get($options,
            'verbose') ? OutputInterface::VERBOSITY_VERY_VERBOSE : OutputInterface::VERBOSITY_NORMAL;
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
     * @return MockInterface
     */
    protected function getConnectionsFactory($expectations = null)
    {
        $me = $this;

        /** @var ConnectionsFactory $factory */
        $factory = $this->prophesize(ConnectionsFactory::class);
        $factory->make(Argument::type(ConnectionKey::class))->will(function ($arguments) use ($me, $expectations) {
            $connection = $expectations instanceof MockInterface ? $expectations : $me->getRemote($expectations);
            $connection->setConnectionKey($arguments[0]);
            if ($adapter = $me->files->getAdapter()) {
                $connection->setAdapter($adapter);
            }

            return $connection;
        });

        return $factory->reveal();
    }

    /**
     * Mock the Remote component.
     *
     * @param string|array|null $mockedOutput
     *
     * @return Mockery|Connection
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

        $connection = Mockery::mock(Connection::class)->makePartial();
        $connection->shouldReceive('isConnected')->andReturn(true)->byDefault();
        $connection->shouldReceive('status')->andReturn(0)->byDefault();

        if (is_array($mockedOutput)) {
            $mockedOutput['bash --login -c \'echo ROCKETEER\''] = 'Inappropriate ioctl for device'.PHP_EOL.'ROCKETEER';
            foreach ($mockedOutput as $command => $output) {
                $connection->shouldReceive('run')->with($command, Mockery::any())->andReturnUsing($mockedRun($output));
                $connection->shouldReceive('run')->with([$command],
                    Mockery::any())->andReturnUsing($mockedRun($output));
            }
        } else {
            $connection->shouldReceive('run')->andReturnUsing($run)->byDefault();
        }

        return $connection;
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
                'scm' => 'git',
                'branch' => 'master',
                'repository' => 'https://github.com/'.$this->repository,
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
