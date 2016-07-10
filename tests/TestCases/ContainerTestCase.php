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
use League\Container\ContainerAwareTrait;
use League\Flysystem\Filesystem;
use League\Flysystem\MountManager;
use League\Flysystem\Vfs\VfsAdapter;
use Mockery;
use Mockery\MockInterface;
use PHPUnit_Framework_TestCase;
use Prophecy\Argument;
use Rocketeer\Console\Commands\AbstractCommand;
use Rocketeer\Console\StyleInterface;
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
use Rocketeer\Traits\HasLocator;
use Symfony\Component\Console\Output\OutputInterface;
use VirtualFileSystem\FileSystem as Vfs;

abstract class ContainerTestCase extends PHPUnit_Framework_TestCase
{
    use Mocks;
    use Assertions;
    use Contexts;
    use Building;
    use HasLocator;
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

        // Paths -------------------------------------------------------- /

        $this->container->add('path.base', '/src');
        $this->container->add('path', $this->container->get('path.base').'/app');
        $this->container->add('path.public', $this->container->get('path.base').'/public');
        $this->container->add('path.storage', $this->container->get('path').'/storage');

        // Replace some instances with mocks
        $this->container->share(ConnectionsFactory::class, function () {
            return $this->getConnectionsFactory();
        });

        $this->container->share('rocketeer.command', function () {
            return $this->getCommand();
        });

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
     *
     * @return Mockery
     */
    protected function getCommand(array $expectations = [], array $options = [])
    {
        $verbose = array_get($options, 'verbose')
            ? OutputInterface::VERBOSITY_VERY_VERBOSE
            : OutputInterface::VERBOSITY_NORMAL;

        /** @var AbstractCommand $command */
        $command = $this->prophesize(AbstractCommand::class)
            ->willImplement(OutputInterface::class)
            ->willImplement(StyleInterface::class);

        $command->getVerbosity()->willReturn($verbose);

        // Bind the output expectations
        $types = ['writeln', 'comment'];
        foreach ($types as $type) {
            if (!array_key_exists($type, $expectations)) {
                $command->$type(Argument::any())->willReturnArgument(0);
            }
        }

        // Merge defaults
        $expectations = array_merge([
            'argument' => '',
            'ask' => '',
            'askWith' => '',
            'table' => '',
            'confirm' => true,
            'option' => false,
        ], $expectations);

        // Bind expecations
        foreach ($expectations as $key => $value) {
            if ($key === 'option') {
                $command->option(Argument::that(function ($argument) use ($options) {
                    return is_null($argument) ? !$options : !in_array($argument, array_keys($options), true);
                }))->willReturn($value);
            } else {
                $returnMethod = $value instanceof Closure ? 'will' : 'willReturn';
                $command->$key(Argument::cetera())->$returnMethod($value);
            }
        }

        // Bind options
        if ($options) {
            $command->option()->willReturn($options);
            foreach ($options as $key => $value) {
                $command->option($key)->willReturn($value);
            }
        }

        return $command->reveal();
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
     * @param string|array|null $expectations
     *
     * @return Mockery|Connection
     */
    protected function getRemote($expectations = null)
    {
        $lookup = 'bash --login -c \'echo ROCKETEER\'';
        $mockedRun = function ($output) {
            return function ($task, $callback) use ($output) {
                $callback($output);
            };
        };

        $run = function ($task, $callback) use ($expectations, $lookup) {
            if (is_array($task)) {
                $task = implode(' && ', $task);
            } elseif ($task === $lookup) {
                $callback('Inappropriate ioctl for device'.PHP_EOL.'ROCKETEER');
            }

            $output = $expectations ? $expectations : shell_exec($task);
            $callback($output);

            return $output;
        };

        $connection = Mockery::mock(Connection::class)->makePartial();
        $connection->shouldReceive('isConnected')->andReturn(true)->byDefault();
        $connection->shouldReceive('status')->andReturn(0)->byDefault();

        if (is_array($expectations)) {
            $expectations[$lookup] = 'Inappropriate ioctl for device'.PHP_EOL.'ROCKETEER';
            foreach ($expectations as $command => $output) {
                $connection->shouldReceive('run')->with($command, Mockery::any())->andReturnUsing($mockedRun($output));
                $connection->shouldReceive('run')->with([$command], Mockery::any())->andReturnUsing($mockedRun($output));
            }
        } else {
            $connection->shouldReceive('run')->andReturnUsing($run)->byDefault();
        }

        return $connection;
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
}
