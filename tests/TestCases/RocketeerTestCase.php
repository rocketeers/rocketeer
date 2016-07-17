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

use Rocketeer\Services\Storages\Storage;

abstract class RocketeerTestCase extends ContainerTestCase
{
    /**
     * The test repository.
     *
     * @var string
     */
    protected $repository = 'Anahkiasen/html-object.git';

    /**
     * @var string
     */
    protected $username = 'anahkiasen';

    /**
     * @var string
     */
    protected $password = 'foobar';

    /**
     * @var string
     */
    protected $host = 'some.host';

    /**
     * @var string
     */
    protected $key = '/.ssh/id_rsa';

    /**
     * A dummy AbstractTask to use for helpers tests.
     *
     * @var \Rocketeer\Tasks\AbstractTask
     */
    protected $task;

    /**
     * Cache of the paths to binaries.
     *
     * @var array
     */
    protected static $binaries = [];

    /**
     * Number of files an ls should yield.
     *
     * @var int
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

        // Bind new Storage instance
        $this->container->share('storage.local', function () {
            return new Storage($this->container, 'local', $this->server, 'deployments');
        });

        // Mock OS
        $this->mockOperatingSystem('Linux');

        // Cache paths
        static::$binaries = static::$binaries ?: [
            'bundle' => exec('which bundle') ?: 'bundle',
            'composer' => exec('which composer') ?: 'composer',
            'php' => exec('which php') ?: 'php',
            'phpunit' => exec('which phpunit') ?: 'phpunit',
            'rsync' => exec('which rsync') ?: 'rsync',
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
        $this->files->createDir($this->server);
        $this->files->createDir($this->server.'/shared');
        $this->files->createDir($this->server.'/releases/10000000000000');
        $this->files->createDir($this->server.'/releases/15000000000000');
        $this->files->createDir($this->server.'/releases/20000000000000');
        $this->files->createDir($this->server.'/current');

        $this->files->put($this->server.'/state.json', json_encode([
            '10000000000000' => true,
            '15000000000000' => false,
            '20000000000000' => true,
        ]));

        $this->files->put($this->server.'/deployments.json', json_encode([
            'foo' => 'bar',
            'directory_separator' => '\\/',
            'is_setup' => true,
            'webuser' => ['username' => 'www-data', 'group' => 'www-data'],
            'line_endings' => "\n",
        ]));
    }
}
