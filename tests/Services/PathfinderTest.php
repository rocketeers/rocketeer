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

namespace Rocketeer\Services;

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
        $folder = $this->paths->getFolder('{path.storage}');

        $this->assertEquals($this->server.'/app/storage', $folder);
    }

    public function testCannotReplaceUnexistingPatternsInFolders()
    {
        $folder = $this->paths->getFolder('{path.foobar}');

        $this->assertEquals($this->server.'/', $folder);
    }

    public function testCanReplacePlaceholdersOnWindows()
    {
        $this->app['path.base'] = 'c:\xampp\htdocs\project';
        $this->app['path.foobar'] = 'c:\xampp\htdocs\project\lol';

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
        $home = Pathfinder::getUserHomeFolder();

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
            'rocketeer::paths.php' => '/bin/php',
        ]);
        $path = $this->paths->getPath('php');

        $this->assertEquals('/bin/php', $path);
    }

    public function testCanGetStoragePathWhenNoneBound()
    {
        unset($this->app['path.storage']);

        $storage = $this->paths->getStoragePath();
        $this->assertEquals('.rocketeer', $storage);
    }

    public function testCanGetStoragePathIfUnix()
    {
        $this->app['path.base'] = '/app';
        $this->app['path.storage'] = '/app/local/folder';

        $storage = $this->paths->getStoragePath();
        $this->assertEquals('local/folder', $storage);
    }

    public function testCanGetStorageIfWindows()
    {
        $this->app['path.base'] = 'C:\Sites\app';
        $this->app['path.storage'] = 'C:\Sites\app\local\folder';

        $storage = $this->paths->getStoragePath();
        $this->assertEquals('local/folder', $storage);
    }

    public function testCanGetStorageWhenBothForSomeReason()
    {
        $this->app['path.base'] = 'C:\Sites\app';
        $this->app['path.storage'] = 'C:/Sites/app/local/folder';

        $storage = $this->paths->getStoragePath();
        $this->assertEquals('local/folder', $storage);
    }

    public function testCanReplacePatternsWithPathsFile()
    {
        $this->swapConfig(['rocketeer::paths.foo' => 'bar']);
        $replaced = $this->paths->replacePatterns('{foo}');

        $this->assertEquals('bar', $replaced);
    }
}
