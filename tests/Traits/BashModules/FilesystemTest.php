<?php

/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Rocketeer\Traits\BashModules;

use Rocketeer\TestCases\RocketeerTestCase;

class FilesystemTest extends RocketeerTestCase
{
    public function testCancelsSymlinkForUnexistingFolders()
    {
        $task   = $this->pretendTask();
        $folder = '{path.storage}/logs';
        $share  = $task->share($folder);

        $this->assertFalse($share);
    }

    public function testCanSymlinkFolders()
    {
        // Create dummy file
        $folder = $this->server.'/releases/20000000000000/src';
        $this->files->createDir($folder);
        $this->files->write($folder.'/foobar.txt', 'test');

        $task     = $this->pretendTask();
        $folder   = '{path.base}/foobar.txt';
        $share    = $task->share($folder);
        $tempLink = $this->server.'/releases/20000000000000//src/foobar.txt-temp';
        $matcher  = [
            sprintf('ln -s %s %s', $this->server.'/shared//src/foobar.txt', $tempLink, $tempLink),
            sprintf('mv -Tf %s %s', $tempLink, $this->server.'/releases/20000000000000//src/foobar.txt'),
        ];

        $this->assertEquals($matcher, $share);
    }

    public function testCanCreateRelativeSymlinks()
    {
        $this->swapConfig(['remote.symlink' => 'relative']);

        // Create dummy file
        $folder = $this->server.'/releases/20000000000000/src';
        $this->files->createDir($folder);
        $this->files->write($folder.'/foobar.txt', 'test');

        $task     = $this->pretendTask();
        $folder   = '{path.base}/foobar.txt';
        $share    = $task->share($folder);
        $tempLink = $this->server.'/releases/20000000000000//src/foobar.txt-temp';
        $matcher  = [
            sprintf('ln -s %s %s', 'shared//src/foobar.txt', $tempLink, $tempLink),
            sprintf('mv -Tf %s %s', $tempLink, $this->server.'/releases/20000000000000//src/foobar.txt'),
        ];

        $this->assertEquals($matcher, $share);
    }

    public function testCanOverwriteFolderWithSymlink()
    {
        $this->mockOperatingSystem();

        // Create dummy folders
        $folderCurrent = $this->server.'/dummy-current';
        $this->files->createDir($folderCurrent);
        $folderRelease = $this->server.'/dummy-release';
        $this->files->createDir($folderRelease);

        $this->bash->symlink($folderRelease, $folderCurrent);

        clearstatcache();

        $this->assertTrue($this->files->isDirectory($folderCurrent));
        $this->assertTrue(is_link($folderCurrent));
    }

    public function testCanListContentsOfAFolder()
    {
        $contents = $this->task->listContents($this->server);

        $this->assertContains('current', $contents);
        $this->assertContains('releases', $contents);
        $this->assertContains('shared', $contents);
        $this->assertContains('state.json', $contents);
    }

    public function testCanCheckIfFileExists()
    {
        $this->assertTrue($this->task->fileExists($this->server));
        $this->assertFalse($this->task->fileExists($this->server.'/nope'));
    }

    public function testDoesntTryToMoveUnexistingFolders()
    {
        $this->pretendTask()->move('foobar', 'bazqux');

        $this->assertEmpty($this->history->getFlattenedOutput());
    }

    public function testCanTailFile()
    {
        $contents = $this->task()->tail($this->server.'/state.json', false);

        $this->assertEquals($this->files->read($this->server.'/state.json'), $contents);
    }
}
