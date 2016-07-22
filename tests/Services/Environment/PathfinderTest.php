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

namespace Rocketeer\Services\Environment;

use Rocketeer\Dummies\DummyPathfinder;
use Rocketeer\Services\Environment\Modules\HomePathfinder;
use Rocketeer\TestCases\RocketeerTestCase;

class PathfinderTest extends RocketeerTestCase
{
    public function testCanGetHomeFolder()
    {
        $this->assertEquals($this->server, $this->paths->getHomeFolder());
    }

    public function testCanGetFolderWithStage()
    {
        $this->connections->setStage('test');

        $this->assertEquals($this->server.'/test/current', $this->paths->getFolder('current'));
    }

    public function testCanGetAnyFolder()
    {
        $this->assertEquals($this->server.'/current', $this->paths->getFolder('current'));
    }

    public function testCanReplacePatternsInFolders()
    {
        $this->container->add('path.storage', 'storage');
        $folder = $this->paths->getFolder('{path.storage}');

        $this->assertEquals($this->server.'/storage', $folder);
    }

    public function testCannotReplaceUnexistingPatternsInFolders()
    {
        $folder = $this->paths->getFolder('{path.foobar}');

        $this->assertEquals($this->server.'/', $folder);
    }

    public function testCanReplacePlaceholdersOnWindows()
    {
        $this->container->add('path.base', 'c:\xampp\htdocs\project');
        $this->container->add('path.foobar', 'c:\xampp\htdocs\project\lol');

        $this->assertEquals($this->server.'/lol', $this->paths->getFolder('{path.foobar}'));
    }

    public function testCanGetUserHomeFolder()
    {
        $_SERVER['HOME'] = '/some/folder';
        $home = $this->paths->getUserHomeFolder();

        $this->assertEquals('/some/folder', $home);
    }

    public function testCanGetWindowsHomeFolder()
    {
        unset($_SERVER['HOME']);

        $_SERVER['HOMEDRIVE'] = 'C:';
        $_SERVER['HOMEPATH'] = '\Users\someuser';
        $home = $this->paths->getUserHomeFolder();

        $this->assertEquals('C:\Users\someuser', $home);
    }

    public function testCanGetWindowsHomeFolderStatically()
    {
        unset($_SERVER['HOME']);

        $_SERVER['HOMEDRIVE'] = 'C:';
        $_SERVER['HOMEPATH'] = '\Users\someuser';
        $home = HomePathfinder::getUserHomeFolder();

        $this->assertEquals('C:\Users\someuser', $home);
    }

    public function testCancelsIfNoHomeFolder()
    {
        $this->setExpectedException('Exception');

        $_SERVER['HOME'] = null;
        $_SERVER['HOMEDRIVE'] = 'C:';
        $_SERVER['HOMEPATH'] = null;
        $this->paths->getUserHomeFolder();
    }

    public function testCanGetRocketeerFolder()
    {
        $_SERVER['HOME'] = '/some/folder';
        $rocketeer = $this->paths->getRocketeerConfigFolder();

        $this->assertEquals('/some/folder/.rocketeer', $rocketeer);
    }

    public function testCanGetBoundPath()
    {
        $this->swapConfig([
            'paths.php' => '/bin/php',
        ]);
        $path = $this->paths->getPath('php');

        $this->assertEquals('/bin/php', $path);
    }

    public function testCanGetStoragePathIfUnix()
    {
        $this->container->add('path.base', '/app');

        $storage = $this->paths->getStoragePath();
        $this->assertEquals('/tmp/rocketeer', $storage);
    }

    public function testCanGetStorageIfWindows()
    {
        $this->container->add('path.base', 'C:\Sites\app');

        $storage = $this->paths->getStoragePath();
        $this->assertEquals('/tmp/rocketeer', $storage);
    }

    public function testCanGetStorageWhenBothForSomeReason()
    {
        $this->container->add('path.base', 'C:\Sites\app');

        $storage = $this->paths->getStoragePath();
        $this->assertEquals('/tmp/rocketeer', $storage);
    }

    public function testCanReplacePatternsWithPathsFile()
    {
        $this->swapConfig(['paths.foo' => 'bar']);
        $replaced = $this->paths->replacePatterns('{foo}');

        $this->assertEquals('bar', $replaced);
    }

    public function testCanHaveDifferentRootDirectoryPerConnection()
    {
        $this->swapConnections([
            'production' => [
                'root_directory' => $this->server,
            ],
            'staging' => [
                'root_directory' => __DIR__,
            ],
        ]);

        $this->connections->setCurrentConnection('production');
        $path = $this->paths->getHomeFolder();
        $this->assertEquals($this->server.'/foobar', $path);

        $this->connections->setCurrentConnection('staging');
        $path = $this->paths->getHomeFolder();
        $this->assertEquals(__DIR__.'/foobar', $path);
    }

    public function testCanRegisterCustomPathfinder()
    {
        $this->paths->register(new DummyPathfinder());
        $this->assertEquals('foofoo', $this->paths->foobar('foo'));
    }

    public function testDoesntReplaceFoldersBearingApplicationName()
    {
        $this->swapConfig([
            'application_name' => 'foobar',
        ]);

        $this->swapConnections([
            'production' => [
                'root_directory' => '/',
            ],
        ]);

        $folder = $this->paths->getFolder('foo/foobar/baz');
        $this->assertEquals('/foobar/foo/foobar/baz', $folder);
    }

    /**
     * @dataProvider providePaths
     *
     * @param string $from
     * @param string $to
     * @param string $expected
     */
    public function testCanComputeRelativePathBetweenTwoFiles($from, $to, $expected)
    {
        $result = $this->paths->computeRelativePathBetween($from, $to);
        $this->assertEquals($expected, $result);
    }

    public function providePaths()
    {
        return [
            ['foo.txt', 'bar.txt', 'bar.txt'],
            ['foo/', 'bar/bar.txt', '../bar/bar.txt'],
            ['foo.txt', './bar.txt', 'bar.txt'],
            ['../foo.txt', '../bar/bar.txt', 'bar/bar.txt'],
            ['foo/foo.txt', 'bar/bar.txt', '../bar/bar.txt'],
            ['foo/bar/../baz.txt', 'foo/baz/baz.txt', 'baz/baz.txt'],
            ['foo/bar/../../baz.txt', 'foo/baz/baz.txt', 'foo/baz/baz.txt'],
            ['foo/bar/bar.txt', 'foo/baz/baz.txt', '../baz/baz.txt'],
            ['foo//foo/foo.txt', 'bar//bar/bar.txt', '../../bar/bar/bar.txt'],
        ];
    }
}
