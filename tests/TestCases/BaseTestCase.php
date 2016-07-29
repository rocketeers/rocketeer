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

use PHPUnit_Framework_TestCase;

abstract class BaseTestCase extends PHPUnit_Framework_TestCase
{
    /**
     * The path to the local fake server.
     *
     * @var string
     */
    protected $server;

    /**
     * @var string
     */
    protected $home;

    /**
     * Cache of the paths to binaries.
     *
     * @var array
     */
    protected static $binaries = [];

    /**
     * Set up the tests.
     */
    public function setUp()
    {
        $this->home = $_SERVER['HOME'];
        $this->server = realpath(__DIR__.'/../_server').'/foobar';

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
        $_SERVER['HOME'] = $this->home;
    }
}
