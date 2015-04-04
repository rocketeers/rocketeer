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

use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use Rocketeer\Services\Filesystem\Plugins\IsDirectoryPlugin;
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
    protected static $currentFiles;

    /**
     * Set up the tests.
     */
    public function setUp()
    {
        parent::setUp();

        // Compute ls results
        $files = preg_grep('/^([^.0])/', scandir(__DIR__.'/../..'));
        sort($files);

        static::$currentFiles = array_values($files);

        // Bind dummy AbstractTask
        $this->task = $this->task('Cleanup');

        $this->recreateVirtualServer();

        // Bind new LocalStorage instance
        $this->app->singleton('rocketeer.storage.local', function ($app) {
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

    protected function recreateVirtualServer()
    {
        // Save superglobals
        $root       = realpath(__DIR__.'/../../').'/';
        $filesystem = new Filesystem(new Local($root));
        $filesystem->addPlugin(new IsDirectoryPlugin());

        @unlink($this->server.'/current');
        @unlink($this->server.'/dummy-current');

        // Cleanup files created by tests
        $cleanup = [
            str_replace($root, null, realpath(__DIR__.'/../../app')),
            str_replace($root, null, realpath(__DIR__.'/../../.rocketeer')),
            str_replace($root, null, realpath(__DIR__.'/../.rocketeer')),
            str_replace($root, null, realpath($this->server)),
            str_replace($root, null, realpath($this->customConfig)),
        ];
        $cleanup = array_filter($cleanup);
        $cleanup = array_filter($cleanup, [$filesystem, 'isDirectory']);
        array_map([$filesystem, 'deleteDir'], $cleanup);

        // Recreate altered local server
        exec(sprintf('rm -rf %s', $this->server));
        exec(sprintf('cp -a %s %s', $this->server.'-stub', $this->server));
    }
}
