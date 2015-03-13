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

use Rocketeer\Services\Storages\LocalStorage;

abstract class RocketeerTestCase extends ContainerTestCase
{
    /**
     * The test repository.
     *
     * @type string
     */
    protected $repository = 'Anahkiasen/html-object.git';

    /**
     * @type string
     */
    protected $username = 'anahkiasen';

    /**
     * @type string
     */
    protected $password = 'foobar';

    /**
     * @type string
     */
    protected $host = 'some.host';

    /**
     * @type string
     */
    protected $key = '/.ssh/id_rsa';

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
     * A dummy AbstractTask to use for helpers tests.
     *
     * @type \Rocketeer\Abstracts\AbstractTask
     */
    protected $task;

    /**
     * Cache of the paths to binaries.
     *
     * @type array
     */
    protected $binaries = [];

    /**
     * Number of files an ls should yield.
     *
     * @type int
     */
    protected static $numberFiles;

    /**
     * Set up the tests.
     */
    public function setUp()
    {
        parent::setUp();

        // Compute ls results
        if (!static::$numberFiles) {
            $files               = preg_grep('/^([^.0])/', scandir(__DIR__.'/../..'));
            static::$numberFiles = count($files);
        }

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
        $this->usesLaravel(true);
        $this->mockOperatingSystem('Linux');

        // Cache paths
        $this->binaries = $this->binaries ?: [
            'php'      => exec('which php') ?: 'php',
            'bundle'   => exec('which bundle') ?: 'bundle',
            'phpunit'  => exec('which phpunit') ?: 'phpunit',
            'composer' => exec('which composer') ?: 'composer',
        ];
    }

    /**
     * Cleanup tests.
     */
    public function tearDown()
    {
        parent::tearDown();

        // Restore superglobals
        $_SERVER['HOME'] = $this->home;
    }

    /**
     * Recreates the local file server.
     */
    protected function recreateVirtualServer()
    {
        // Save superglobals
        $this->home = $_SERVER['HOME'];

        // Cleanup files created by tests
        $cleanup = [
            realpath(__DIR__.'/../../.rocketeer'),
            realpath(__DIR__.'/../.rocketeer'),
            realpath($this->server),
            realpath($this->customConfig),
        ];
        array_map([$this->files, 'deleteDirectory'], $cleanup);

        // Recreate altered local server
        exec(sprintf('rm -rf %s', $this->server));
        exec(sprintf('cp -a %s %s', $this->server.'-stub', $this->server));
    }
}
