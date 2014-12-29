<?php
namespace Rocketeer\TestCases;

use Rocketeer\Services\Storages\LocalStorage;
use Symfony\Component\Console\Tester\CommandTester;

abstract class RocketeerTestCase extends ContainerTestCase
{
    /**
     * The path to the local fake server
     *
     * @var string
     */
    protected $server;

    /**
     * @type string
     */
    protected $customConfig;

    /**
     * The path to the local deployments file
     *
     * @var string
     */
    protected $deploymentsFile;

    /**
     * A dummy AbstractTask to use for helpers tests
     *
     * @var \Rocketeer\Abstracts\AbstractTask
     */
    protected $task;

    /**
     * Cache of the paths to binaries
     *
     * @type array
     */
    protected $binaries = [];

    /**
     * Number of files an ls should yield
     *
     * @type integer
     */
    protected $numberFiles = 11;

    /**
     * Set up the tests
     */
    public function setUp()
    {
        parent::setUp();

        // Setup local server
        $this->server          = __DIR__.'/../_server/foobar';
        $this->customConfig    = $this->server.'/../.rocketeer';
        $this->deploymentsFile = $this->server.'/deployments.json';

        // Bind dummy AbstractTask
        $this->task = $this->task('Cleanup');
        $this->recreateVirtualServer();

        // Bind new LocalStorage instance
        $this->app->bind('rocketeer.storage.local', function ($app) {
            $folder = dirname($this->deploymentsFile);

            return new LocalStorage($app, 'deployments', $folder);
        });

        // Mock OS
        $this->mockOperatingSystem('Linux');

        // Cache paths
        $this->binaries = $this->binaries ?: array(
            'php'      => exec('which php') ?: 'php',
            'bundle'   => exec('which bundle') ?: 'bundle',
            'phpunit'  => exec('which phpunit') ?: 'phpunit',
            'composer' => exec('which composer') ?: 'composer',
        );
    }

    /**
     * Cleanup tests
     */
    public function tearDown()
    {
        parent::tearDown();

        // Restore superglobals
        $_SERVER['HOME'] = $this->home;
    }

    /**
     * Recreates the local file server
     *
     * @return void
     */
    protected function recreateVirtualServer()
    {
        // Save superglobals
        $this->home = $_SERVER['HOME'];

        // Cleanup files created by tests
        $cleanup = array(
            realpath(__DIR__.'/../../.rocketeer'),
            realpath(__DIR__.'/../.rocketeer'),
            realpath($this->server),
            realpath($this->customConfig),
        );
        array_map([$this->files, 'deleteDirectory'], $cleanup);

        // Recreate altered local server
        exec(sprintf('rm -rf %s', $this->server));
        exec(sprintf('cp -a %s %s', $this->server.'-stub', $this->server));
    }

    ////////////////////////////////////////////////////////////////////
    /////////////////////////////// HELPERS ////////////////////////////
    ////////////////////////////////////////////////////////////////////

    /**
     * Get and execute a command
     *
     * @param string|null $command
     * @param array       $options
     *
     * @return CommandTester
     */
    protected function executeCommand($command = null, $options = array())
    {
        // Fetch command
        $command = $command ? '.'.$command : null;
        $command = $this->app['rocketeer.commands'.$command];

        // Build options
        $options = array_merge(array(
            'command' => $command->getName(),
        ), $options);

        // Execute
        $tester = new CommandTester($command);
        $tester->execute($options);

        return $tester;
    }

    /**
     * Get a pretend AbstractTask to run bogus commands
     *
     * @param string $task
     * @param array  $options
     * @param array  $expectations
     *
     * @return \Rocketeer\Abstracts\AbstractTask
     */
    protected function pretendTask($task = 'Deploy', $options = array(), array $expectations = array())
    {
        $this->pretend($options, $expectations);

        return $this->task($task);
    }

    /**
     * Get AbstractTask instance
     *
     * @param string $task
     * @param array  $options
     *
     * @return \Rocketeer\Abstracts\AbstractTask
     */
    protected function task($task = null, $options = array())
    {
        if ($options) {
            $this->mockCommand($options);
        }

        if (!$task) {
            return $this->task;
        }

        return $this->builder->buildTaskFromClass($task);
    }
}
